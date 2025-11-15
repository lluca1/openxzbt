using UnityEngine;
using System;
using System.Linq;

public class DataLoader : MonoBehaviour
{
    private const string ASSET_HOST_URL = "https://unihack-2025-ereg-vd8nga8j.on-forge.com/storage/";

    private ModelLoader modelLoader;

    private static DataLoader instance;

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

        modelLoader = GetComponent<ModelLoader>();
    }

    public void LoadModel(string modelUrl, string modelId, Action<GameObject> onLoadedModel)
    {
        string baseUrl = $"{ASSET_HOST_URL}{modelUrl}";
        string objFilename = $"{baseUrl}/{modelId}.obj";
        string mtlFilename = $"{baseUrl}/{modelId}.mtl";

        modelLoader.LoadObj(baseUrl, objFilename, mtlFilename, onLoadedModel);
    }

    public void LoadTileData(string expoId, string tileId, Action<TileData> onLoadedTileData)
    {
        
    }

    public void LoadTexture(string expoID, Action<Texture2D> onLoadedTexture)
    {

    }

    public void LoadAudio(string expoID, Action onLoadedAudio)
    {

    }

    public void LoadExpoData(string expoID, Action<ExpoData> onLoadedExpoData)
    {
        string endpoint = $"{expoID}";

        ServerCommunicator.Instance.GetRequest(endpoint, (success, jsonResponse, error) =>
        {
            if (success)
            {
                try
                {
                    ExpoRootData expoRootData = JsonUtility.FromJson<ExpoRootData>(jsonResponse);

                    onLoadedExpoData?.Invoke(expoRootData.data);
                    Debug.Log($"Successfully loaded Expo configuration: {expoRootData.data.title}");
                }
                catch (Exception e)
                {
                    Debug.LogError($"Error deserializing Expo JSON: {e.Message}");
                    onLoadedExpoData?.Invoke(null);
                }
            }
            else
            {
                Debug.LogError($"Failed to download Expo data from {endpoint}: {error}");
                onLoadedExpoData?.Invoke(null);
            }
        });
    }
}