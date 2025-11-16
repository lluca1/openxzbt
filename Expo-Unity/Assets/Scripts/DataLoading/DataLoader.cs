using UnityEngine;
using System;
using System.Collections;
using UnityEngine.Networking;

public class DataLoader : MonoBehaviour
{
    private const string ASSET_HOST_URL = "https://openxzbt.art/storage/";
    private const string BASE_URL = "https://openxzbt.art";

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

    public void LoadTexture(string textureUrl, Action<Texture2D> onLoadedTexture)
    {
        StartCoroutine(LoadTextureCoroutine(textureUrl, onLoadedTexture));
    }

    private IEnumerator LoadTextureCoroutine(string textureUrl, Action<Texture2D> onLoadedTexture)
    {
        string url = $"{BASE_URL}{textureUrl}";

        using (UnityWebRequest uwr = UnityWebRequestTexture.GetTexture(url))
        {
            yield return uwr.SendWebRequest();

            if (uwr.result == UnityWebRequest.Result.ConnectionError || uwr.result == UnityWebRequest.Result.ProtocolError)
            {
                Debug.LogError($"Error loading texture from {url} ({textureUrl}): {uwr.error}");
                onLoadedTexture?.Invoke(null);
            }
            else
            {
                Texture2D texture = DownloadHandlerTexture.GetContent(uwr);
                onLoadedTexture?.Invoke(texture);
            }
        }
    }

    public void LoadAudio(string audioUrl, Action<AudioClip> onLoadedAudio)
    {
        StartCoroutine(LoadAudioCoroutine(audioUrl, onLoadedAudio));
    }

    private IEnumerator LoadAudioCoroutine(string audioUrl, Action<AudioClip> onLoadedAudio)
    {
        // Assuming the ambient audio file is named 'ambient.mp3' or 'ambient.wav'
        // We use .mp3 for this example and AudioType.MPEG. 
        string url = $"{BASE_URL}{audioUrl}";

        using (UnityWebRequest uwr = UnityWebRequestMultimedia.GetAudioClip(url, AudioType.MPEG))
        {
            yield return uwr.SendWebRequest();

            if (uwr.result == UnityWebRequest.Result.ConnectionError || uwr.result == UnityWebRequest.Result.ProtocolError)
            {
                Debug.LogError($"Error loading audio from {url}: {uwr.error}");
                onLoadedAudio?.Invoke(null);
            }
            else
            {
                AudioClip audioClip = DownloadHandlerAudioClip.GetContent(uwr);
                audioClip.name = $"Ambient_{audioUrl}";
                onLoadedAudio?.Invoke(audioClip);
            }
        }
    }

    public void LoadExpoData(string expoID, Action<ExpoData> onexpoData)
    {
        string endpoint = $"{expoID}";

        ServerCommunicator.Instance.GetRequest(endpoint, (success, jsonResponse, error) =>
        {
            if (success)
            {
                try
                {
                    ExpoRootData expoRootData = JsonUtility.FromJson<ExpoRootData>(jsonResponse);

                    onexpoData?.Invoke(expoRootData.data);
                    Debug.Log($"Successfully loaded Expo configuration: {expoRootData.data.title}");
                }
                catch (Exception e)
                {
                    Debug.LogError($"Error deserializing Expo JSON: {e.Message}");
                    onexpoData?.Invoke(null);
                }
            }
            else
            {
                Debug.LogError($"Failed to download Expo data from {endpoint}: {error}");
                onexpoData?.Invoke(null);
            }
        });
    }
}