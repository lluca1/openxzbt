using UnityEngine;
using UnityEngine.Networking;
using System.Collections;
using System.IO;
using Dummiesman;
using System;
using System.Text;
using System.Text.RegularExpressions;
using System.Collections.Generic;
using System.Linq; // Added for simplified LINQ use

public class ModelLoader : MonoBehaviour
{
    // STORAGE_BASE_URL is no longer needed as textures are co-located with OBJ/MTL

    private static ModelLoader instance;

    public static ModelLoader Instance
    {
        get
        {
            if (instance == null)
            {
                instance = FindFirstObjectByType<ModelLoader>();
                if (instance == null)
                {
                    GameObject singletonObject = new GameObject(typeof(ModelLoader).Name);
                    instance = singletonObject.AddComponent<ModelLoader>();
                }
            }
            return instance;
        }
    }

    private void Awake()
    {
        if (instance == null)
        {
            instance = this;
        }
        else if (instance != this)
        {
            Destroy(gameObject);
        }
    }

    /// <summary>
    /// Parses the MTL content to create a map between Material Names and their diffuse Texture File Names (map_Kd).
    /// </summary>
    /// <param name="mtlContent">The content of the MTL file.</param>
    /// <returns>Dictionary where Key=Material Name, Value=Texture File Name.</returns>
    private Dictionary<string, string> ExtractMaterialTextureMap(string mtlContent)
    {
        // Key: Material Name (from newmtl), Value: Texture File Name (from map_Kd)
        var materialTextureMap = new Dictionary<string, string>();
        string currentMaterial = null;

        string[] lines = mtlContent.Split(new[] { '\r', '\n' }, StringSplitOptions.RemoveEmptyEntries);

        foreach (string line in lines)
        {
            string trimmedLine = line.Trim();

            if (trimmedLine.StartsWith("newmtl "))
            {
                // Found a new material definition
                currentMaterial = trimmedLine.Substring(7).Trim();
            }
            else if (currentMaterial != null && trimmedLine.StartsWith("map_Kd "))
            {
                // Found a diffuse map for the current material
                string textureFileName = trimmedLine.Substring(7).Trim();

                if (!materialTextureMap.ContainsKey(currentMaterial))
                {
                    materialTextureMap.Add(currentMaterial, textureFileName);
                }
                // Reset currentMaterial if needed, but MTLs often list properties sequentially, so we keep it.
                // We assume one map_Kd per newmtl block is sufficient for diffuse texture.
            }
        }
        return materialTextureMap;
    }

    // Arguments match DataLoader's call: modelId is now ignored for path construction.
    public void LoadObj(string objBaseUrl, string objUrl, string mtlUrl, Action<GameObject> onLoadedModel)
    {
        // objBaseUrl is used for the texture path.
        // The modelId argument is retained to match the public signature but is unused in the texture path.
        StartCoroutine(LoadModelFromURL(objBaseUrl, objUrl, mtlUrl, onLoadedModel));
    }

    /// <summary>
    /// Downloads a texture using the base exhibit URL and the filename extracted from the MTL.
    /// </summary>
    private IEnumerator DownloadTexture(string baseUrl, string textureFileName, Action<Texture2D> onTextureLoaded)
    {
        // Construct the texture URL using the same base path as the OBJ/MTL
        string textureUrl = baseUrl;

        // Ensure only a single slash separates the base URL and the filename
        if (baseUrl.EndsWith("/") && textureFileName.StartsWith("/"))
        {
            textureUrl += textureFileName.TrimStart('/');
        }
        else if (!baseUrl.EndsWith("/") && !textureFileName.StartsWith("/"))
        {
            textureUrl += "/" + textureFileName;
        }
        else
        {
            textureUrl += textureFileName;
        }

        using (UnityWebRequest uwr = UnityWebRequestTexture.GetTexture(textureUrl))
        {
            Debug.Log($"[ModelLoader] Downloading texture: {textureUrl}");
            yield return uwr.SendWebRequest();

            if (uwr.result == UnityWebRequest.Result.Success)
            {
                Texture2D texture = DownloadHandlerTexture.GetContent(uwr);
                onTextureLoaded?.Invoke(texture);
                Debug.Log($"[ModelLoader] Successfully downloaded texture: {textureFileName}");
            }
            else
            {
                Debug.LogError($"[ModelLoader] Error downloading texture ({textureUrl}): {uwr.error}");
                onTextureLoaded?.Invoke(null);
            }
        }
    }

    // MODIFIED: Removed modelId argument to simplify.
    private IEnumerator LoadModelFromURL(string baseUrl, string objUrl, string mtlUrl, Action<GameObject> onLoadedModel)
    {
        string mtlContent = null;
        // Key: Material Name, Value: Texture File Name
        Dictionary<string, string> materialToTextureFileName = new Dictionary<string, string>();
        // Key: Texture File Name, Value: Downloaded Texture2D
        Dictionary<string, Texture2D> downloadedTextures = new Dictionary<string, Texture2D>();

        // --- 1. Download the MTL File ---
        using (UnityWebRequest mtlUwr = UnityWebRequest.Get(mtlUrl))
        {
            Debug.Log($"[ModelLoader] Downloading MTL file: {mtlUrl}");
            yield return mtlUwr.SendWebRequest();
            if (mtlUwr.result == UnityWebRequest.Result.Success)
            {
                mtlContent = mtlUwr.downloadHandler.text;
                Debug.Log($"[ModelLoader] Successfully downloaded MTL content for: {objUrl}");
            }
            else
            {
                Debug.LogWarning($"[ModelLoader] Could not download MTL file ({mtlUrl}): {mtlUwr.error}. Proceeding without MTL.");
            }
        }

        // --- 2. Extract Texture Names and Download ALL UNIQUE Textures ---
        if (!string.IsNullOrEmpty(mtlContent))
        {
            // NEW: Get the map of Material Name -> Texture File Name
            materialToTextureFileName = ExtractMaterialTextureMap(mtlContent);

            if (materialToTextureFileName.Count > 0)
            {
                List<Coroutine> textureCoroutines = new List<Coroutine>();

                // Get a list of unique texture file names needed, even if multiple materials use the same one.
                HashSet<string> uniqueTextureFiles = new HashSet<string>(materialToTextureFileName.Values);

                foreach (string textureFileName in uniqueTextureFiles)
                {
                    // Start a new coroutine for each unique texture download
                    Coroutine textureCoroutine = StartCoroutine(DownloadTexture(baseUrl, textureFileName, tex => {
                        if (tex != null)
                        {
                            // Store texture using its filename as the key
                            downloadedTextures[textureFileName] = tex;
                        }
                    }));
                    textureCoroutines.Add(textureCoroutine);
                }

                // Wait for ALL texture downloads to complete before proceeding
                foreach (Coroutine coroutine in textureCoroutines)
                {
                    yield return coroutine;
                }
                Debug.Log($"[ModelLoader] Finished downloading {downloadedTextures.Count} unique textures.");
            }
            else
            {
                Debug.LogWarning($"[ModelLoader] No map_Kd texture paths found in MTL content for {objUrl}.");
            }
        }

        // --- 3. Download the OBJ File and Load Model ---
        using (UnityWebRequest objUwr = UnityWebRequest.Get(objUrl))
        {
            yield return objUwr.SendWebRequest();

            if (objUwr.result != UnityWebRequest.Result.Success)
            {
                Debug.LogError($"[ModelLoader] Error downloading OBJ file ({objUrl}): {objUwr.error}");
                onLoadedModel?.Invoke(null);
                yield break;
            }

            try
            {
                byte[] objData = objUwr.downloadHandler.data;
                GameObject loadedModel = null;

                using (MemoryStream objStream = new MemoryStream(objData))
                {
                    // Load the model using Dummiesman
                    if (!string.IsNullOrEmpty(mtlContent))
                    {
                        byte[] mtlBytes = Encoding.UTF8.GetBytes(mtlContent);
                        using (MemoryStream mtlStream = new MemoryStream(mtlBytes))
                        {
                            loadedModel = new OBJLoader().Load(objStream, mtlStream);
                        }
                    }
                    else
                    {
                        loadedModel = new OBJLoader().Load(objStream);
                    }

                    // --- 4. Apply Downloaded Textures to Correct Materials ---
                    if (loadedModel != null && downloadedTextures.Count > 0)
                    {
                        Renderer[] renderers = loadedModel.GetComponentsInChildren<Renderer>(true);

                        foreach (Renderer renderer in renderers)
                        {
                            // We use .materials to get a writable copy of the materials array.
                            // NOTE: Dummiesman materials are usually marked as 'Clone' by Unity, 
                            // so we match against the base material name.
                            Material[] materials = renderer.materials;

                            for (int i = 0; i < materials.Length; i++)
                            {
                                Material material = materials[i];

                                if (material != null)
                                {
                                    // Get the original material name (before Unity appends ' (Instance)' or '(Clone)')
                                    string originalMaterialName = material.name.Replace(" (Instance)", "").Replace("(Clone)", "").Trim();

                                    // Find the texture filename associated with THIS material name from the MTL map
                                    if (materialToTextureFileName.TryGetValue(originalMaterialName, out string textureFileName))
                                    {
                                        // Now, look up the downloaded Texture2D using the filename as the key
                                        if (downloadedTextures.TryGetValue(textureFileName, out Texture2D texture))
                                        {
                                            // Apply the CORRECT texture to the material's main texture property
                                            material.mainTexture = texture;
                                            Debug.Log($"[ModelLoader] Applied texture {textureFileName} to material {material.name}.");
                                        }
                                        else
                                        {
                                            // This means the file name was in the MTL but the download failed.
                                            Debug.LogWarning($"[ModelLoader] Found texture name '{textureFileName}' for material '{material.name}', but texture failed to download.");
                                        }
                                    }
                                }
                            }
                        }
                    }

                    onLoadedModel?.Invoke(loadedModel);
                    Debug.Log($"[ModelLoader] Successfully completed loading process for: {objUrl}");
                }
            }
            catch (System.Exception e)
            {
                Debug.LogError($"[ModelLoader] OBJ Importer Error on {objUrl}: {e.Message}");
                onLoadedModel?.Invoke(null);
            }
        }
    }
}