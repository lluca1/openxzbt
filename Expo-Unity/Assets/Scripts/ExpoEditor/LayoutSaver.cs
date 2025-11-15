using UnityEngine;
using System.Collections.Generic;
using System.Linq;
using System;
using System.IO;

public class LayoutSaver : MonoBehaviour
{
    private TilePlacer tilePlacer;

    private void Awake()
    {
        tilePlacer = FindFirstObjectByType<TilePlacer>();
    }

    public void SaveLayout(int expoId)
    {
        // 1. Find all placed objects
        GameObject[] placedTiles = GameObject.FindGameObjectsWithTag("PlacedTile");
        GameObject[] placedExhibits = GameObject.FindGameObjectsWithTag("PlacedExhibit");
        GameObject playerSpawn = GameObject.FindGameObjectWithTag("PlayerSpawn");

        List<TileSaveData> tilePayload = new List<TileSaveData>();

        foreach (GameObject tileObject in placedTiles)
        {
            int tileTypeIndex = (int)tileObject.GetComponent<PlacedTileData>().tileType;

            Vector3 pos = tileObject.transform.position;
            Vector3 rot = tileObject.transform.rotation.eulerAngles;

            TileSaveData tileData = new TileSaveData
            {
                id = tilePayload.Count.ToString(),
                type = tileTypeIndex,
                position = new float[] { pos.x, pos.y, pos.z },
                rotation = new float[] { rot.x, rot.y, rot.z }
            };
            tilePayload.Add(tileData);
        }

        List<ExhibitLayoutSaveData> exhibitPayload = new List<ExhibitLayoutSaveData>();

        foreach (GameObject exhibitObject in placedExhibits)
        {
            Vector3 pos = exhibitObject.transform.position;

            ExhibitLayoutSaveData exhibitData = new ExhibitLayoutSaveData
            {
                id = exhibitPayload.Count.ToString(),
                position = new float[] { pos.x, pos.y, pos.z },
                size = 1
            };
            exhibitPayload.Add(exhibitData);
        }

        Vector3 playerSpawnPos = playerSpawn.transform.position;

        LayoutSavePayload payload = new LayoutSavePayload
        {
            playerSpawn = new float[] { playerSpawnPos.x, playerSpawnPos.y, playerSpawnPos.z },
            tiles = tilePayload,
            exhibits = exhibitPayload
        };


        string jsonData = JsonUtility.ToJson(payload);

        // local json
        /*

        string rawPath = Path.Combine(Application.dataPath, $"ExpoLayout_{expoId}.json");
        string correctedPath = Path.GetFullPath(rawPath);

        try
        {
            File.WriteAllText(correctedPath, jsonData); // Write using the system path
            Debug.Log($"Successfully saved layout to local file: **{correctedPath}**"); // Log the system path
        }
        catch (Exception e)
        {
            Debug.LogError($"Failed to save layout to file: {e.Message}");
        }

        */

        string endpoint = $"{expoId}/layout";

        string sanctumToken = "";
        ServerCommunicator.Instance.PutRequest(endpoint, jsonData, OnLayoutSaveComplete, sanctumToken);
    }

    private void OnLayoutSaveComplete(bool success, string jsonResponse, string error)
    {
        if (success)
        {
            Debug.Log("Layout saved successfully! Server response received");
        }
        else
        {
            Debug.LogError($"Layout save failed: {error}");
        }
    }
}