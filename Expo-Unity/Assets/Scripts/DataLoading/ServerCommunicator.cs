using UnityEngine;
using UnityEngine.Networking;
using System;
using System.Collections;
using System.Text;

public class ServerCommunicator : MonoBehaviour
{
    private const string API_BASE_URL = "https://unihack-2025-ereg-vd8nga8j.on-forge.com/api/expositions/";

    private static ServerCommunicator instance;

    public static ServerCommunicator Instance
    {
        get
        {
            if (instance == null)
            {
                instance = FindFirstObjectByType<ServerCommunicator>();
                if (instance == null)
                {
                    GameObject singletonObject = new GameObject(typeof(ServerCommunicator).Name);
                    instance = singletonObject.AddComponent<ServerCommunicator>();
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
    /// Executes a generic GET request to a specified endpoint.
    /// </summary>
    /// <param name="endpoint">The API endpoint path (e.g., "exhibits/1/info").</param>
    /// <param name="onComplete">Callback function to handle the result (success status, response body, error message).</param>
    public void GetRequest(string endpoint, Action<bool, string, string> onComplete)
    {
        StartCoroutine(SendGetRequest(endpoint, onComplete));
    }

    private IEnumerator SendGetRequest(string endpoint, Action<bool, string, string> onComplete)
    {
        string fullUrl = API_BASE_URL + endpoint;

        using (UnityWebRequest uwr = UnityWebRequest.Get(fullUrl))
        {
            Debug.Log($"[ServerCommunicator] Sending GET request to: {fullUrl}");
            yield return uwr.SendWebRequest();

            if (uwr.result == UnityWebRequest.Result.Success)
            {
                string jsonResponse = uwr.downloadHandler.text;
                Debug.Log($"[ServerCommunicator] GET Success. Response: {jsonResponse.Substring(0, Math.Min(100, jsonResponse.Length))}...");
                onComplete?.Invoke(true, jsonResponse, null);
            }
            else
            {
                Debug.LogError($"[ServerCommunicator] GET Error ({fullUrl}): {uwr.error}");
                onComplete?.Invoke(false, null, uwr.error);
            }
        }
    }

    /// <summary>
    /// Executes a generic PUT request to a specified endpoint with JSON payload.
    /// </summary>
    /// <param name="endpoint">The API endpoint path (e.g., "7/layout").</param>
    /// <param name="jsonData">The JSON string to send in the request body.</param>
    /// <param name="onComplete">Callback function to handle the result (success status, response body, error message).</param>
    /// <param name="bearerToken">The Sanctum token for authorization.</param>
    public void PutRequest(string endpoint, string jsonData, Action<bool, string, string> onComplete, string bearerToken)
    {
        StartCoroutine(SendPutRequest(endpoint, jsonData, onComplete, bearerToken));
    }

    private IEnumerator SendPutRequest(string endpoint, string jsonData, Action<bool, string, string> onComplete, string bearerToken)
    {
        string fullUrl = API_BASE_URL + endpoint;
        byte[] bodyRaw = Encoding.UTF8.GetBytes(jsonData);

        // Create the UnityWebRequest with the correct method
        using (UnityWebRequest uwr = new UnityWebRequest(fullUrl, "PUT"))
        {
            // Set the payload and content type
            uwr.uploadHandler = new UploadHandlerRaw(bodyRaw);
            uwr.downloadHandler = new DownloadHandlerBuffer();
            uwr.SetRequestHeader("Content-Type", "application/json");

            // Add Authorization header (required by the backend)
            //uwr.SetRequestHeader("Authorization", $"Bearer {bearerToken}");

            Debug.Log($"[ServerCommunicator] Sending PUT request to: {fullUrl} with data: {jsonData.Substring(0, Math.Min(100, jsonData.Length))}...");
            yield return uwr.SendWebRequest();

            if (uwr.result == UnityWebRequest.Result.Success)
            {
                string jsonResponse = uwr.downloadHandler.text;
                Debug.Log($"[ServerCommunicator] PUT Success. Response: {jsonResponse.Substring(0, Math.Min(100, jsonResponse.Length))}...");
                onComplete?.Invoke(true, jsonResponse, null);
            }
            else
            {
                Debug.LogError($"[ServerCommunicator] PUT Error ({fullUrl}): {uwr.error} - {uwr.downloadHandler?.text}");
                onComplete?.Invoke(false, null, uwr.error);
            }
        }
    }
}