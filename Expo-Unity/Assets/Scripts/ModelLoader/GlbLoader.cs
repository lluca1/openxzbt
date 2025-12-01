using UnityEngine;
using UnityEngine.Networking;
using System.Collections;
using System.IO;
using System;
using UnityGLTF;

/*public class GlbLoader : MonoBehaviour
{
    // Example URL for a public GLB model (Khronos Box) for testing purposes
    // Replace this with your actual URL or a method to generate it.
    private const string GLB_URL = "https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/Box/glTF-Binary/Box.glb";
    
    private static GlbLoader instance;

    private void Awake()
    {
        // Simple Singleton pattern
        if (instance == null)
        {
            instance = this;
        }
        else
        {
            Destroy(gameObject);
        }
    }

    /// <summary>
    /// Starts the asynchronous loading process for a GLB model from a URL.
    /// </summary>
    /// <param name="url">The direct URL to the .glb file (must be raw content, not a GitHub page).</param>
    /// <param name="onLoadedModel">Callback executed with the loaded GameObject or null on failure.</param>
    public void Load(string url, Action<GameObject> onLoadedModel)
    {
        StartCoroutine(LoadGlbFromURL(url, onLoadedModel));
    }

    // --- Public method for easy testing with the hardcoded example ---
    public void LoadExample(Action<GameObject> onLoadedModel)
    {
        Load(GLB_URL, onLoadedModel);
    }
    // -----------------------------------------------------------------

    private IEnumerator LoadGlbFromURL(string url, Action<GameObject> onLoadedModel)
    {
        Debug.Log($"Starting GLB download from: {url}");

        // --- 1. Download the GLB File (as byte array) ---
        using (UnityWebRequest uwr = UnityWebRequest.Get(url))
        {
            // Set the download handler to download raw bytes for the binary file
            uwr.downloadHandler = new DownloadHandlerBuffer();
            
            yield return uwr.SendWebRequest();

            if (uwr.result != UnityWebRequest.Result.Success)
            {
                Debug.LogError($"Error downloading GLB file ({url}): {uwr.error}");
                onLoadedModel?.Invoke(null);
                yield break;
            }

            byte[] glbData = uwr.downloadHandler.data;
            Debug.Log($"Successfully downloaded {glbData.Length} bytes of GLB data.");

            // --- 2. Load the Model using GLTFUtility ---
            try
            {
                GameObject loadedModel = null;
                bool loadComplete = false;
                
                // GLTFUtility's LoadFromBytes is asynchronous and requires a callback chain
                GLTFUtility.LoadFromBytes(
                    glbData, 
                    transform.parent, // Use the current object's parent as the scene root
                    go => {
                        // Success callback: go is the fully loaded GameObject
                        loadedModel = go;
                        loadComplete = true;
                    }, 
                    progress => {
                        // Progress callback (optional)
                    }, 
                    ex => {
                        // Error callback
                        Debug.LogError($"GLB Import Error: {ex.Message}");
                        loadComplete = true; // Mark as complete even on error to exit the loop
                    }
                );

                // Wait until the asynchronous GLTFUtility loading process is complete
                while (!loadComplete)
                {
                    yield return null;
                }

                // --- 3. Invoke Callback ---
                onLoadedModel?.Invoke(loadedModel);
                Debug.Log($"Successfully loaded GLB model: {url}");
            }
            catch (System.Exception e)
            {
                Debug.LogError($"GLB Loader Exception: {e.Message}");
                onLoadedModel?.Invoke(null);
            }
        }
    }
}*/