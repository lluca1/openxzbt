using UnityEngine;
using System.Collections.Generic;
using UnityEngine.SceneManagement;
using System;
using System.IO;

public class ExpoLayoutEditor : MonoBehaviour
{
    private ExpoData expoData;
    private TilePlacer tilePlacer;

    // NEW FIELDS for Exhibit Placement Limit
    private int maxExhibitCount = 0;
    private int currentExhibitCount = 0;

    private void OnEnable()
    {
        SceneManager.sceneLoaded += OnSceneLoaded;
    }

    private void OnDisable()
    {
        SceneManager.sceneLoaded -= OnSceneLoaded;
    }

    private void OnSceneLoaded(Scene scene, LoadSceneMode loadScene)
    {
        if (scene.buildIndex == SceneLoader.SCENE_INDEX_LAYOUT_EDITOR)
        {
            if (expoData != null)
            {
                CreateLayout();
            }
        }
    }

    private void LoadLayout(ExpoData expoData)
    {
        this.expoData = expoData;

        // NEW: Set the maximum number of exhibits based on the loaded data
        // This assumes expoData.exhibits contains the list of ALL available exhibits for the expo.
        if (this.expoData != null && this.expoData.exhibits != null)
        {
            maxExhibitCount = this.expoData.exhibits.Count;
            Debug.Log($"Max available exhibits for this expo: {maxExhibitCount}");
        }
        else
        {
            maxExhibitCount = 0;
        }

        GameManager.Instance.SceneLoader.LoadLayoutEditor();
    }

    private void CreateLayout()
    {
        tilePlacer = FindFirstObjectByType<TilePlacer>();

        if (tilePlacer == null)
        {
            Debug.LogError("TilePlacer not found! Cannot create layout.");
            return;
        }

        // NEW: Reset current count before loading
        currentExhibitCount = 0;

        // 1. Load Tiles
        foreach (TileData tile in expoData.tiles)
        {
            GameObject tilePrefab = tilePlacer.GetTilePrefab((TileType)tile.type);
            Vector3 pos = tile.GetPosition();
            Quaternion rot = Quaternion.Euler(tile.GetRotation());

            GameObject newTile = Instantiate(tilePrefab, pos, rot);
            newTile.tag = "PlacedTile";

            PlacedTileData dataComponent = newTile.AddComponent<PlacedTileData>();
            dataComponent.Setup((TileType)tile.type);

            dataComponent.hasExhibit = tile.has_exhibit;

            Debug.Log($"Loaded Tile Type {(TileType)tile.type} at {pos}");
        }

        // 2. Load Exhibits
        foreach (ExhibitData exhibitData in expoData.exhibits)
        {
            GameObject exhibitPrefab = tilePlacer.GetExhibitPrefab();
            // Assuming exhibitData.GetPosition() provides the coordinates if the exhibit is placed
            Vector3 pos = exhibitData.GetPosition() + new Vector3(0, 2, 0);

            GameObject newExhibit = Instantiate(exhibitPrefab, pos, Quaternion.identity);
            newExhibit.tag = "PlacedExhibit";

            // NEW: Increment the count of currently placed exhibits
            // This relies on the assumption that every entry in expoData.exhibits 
            // that is loaded here is a successfully placed exhibit.
            currentExhibitCount++;

            Debug.Log($"Loaded Exhibit at {pos}");
        }

        Debug.Log($"Layout created. Current Placed Exhibits: {currentExhibitCount}/{maxExhibitCount}");


        // 3. Load Player Spawn
        GameObject spawnPrefab = tilePlacer.GetPlayerSpawnPrefab();
        Vector3 spawnPos = expoData.GetSpawnpointPosition();

        GameObject newSpawn = Instantiate(spawnPrefab, spawnPos, Quaternion.identity);
        newSpawn.tag = "PlayerSpawn";

        tilePlacer.SetCurrentSpawnPoint(newSpawn);

        Debug.Log($"Loaded Player Spawn at {spawnPos}");
    }

    public void StartLayoutEditor(string expoId)
    {
        GameManager.Instance.DataLoader.LoadExpoData(expoId, LoadLayout);
    }

    public void SaveLayout()
    {
        // 1. Find all placed objects
        GameObject[] placedTiles = GameObject.FindGameObjectsWithTag("PlacedTile");
        GameObject[] placedExhibits = GameObject.FindGameObjectsWithTag("PlacedExhibit");
        GameObject playerSpawn = GameObject.FindGameObjectWithTag("PlayerSpawn");

        List<TileSaveData> tilePayload = new List<TileSaveData>();

        foreach (GameObject tileObject in placedTiles)
        {
            // PlacedTileData component is guaranteed to exist by CreateLayout/FinalizePlacement
            PlacedTileData tileDataComponent = tileObject.GetComponent<PlacedTileData>();
            if (tileDataComponent == null) continue;

            int tileTypeIndex = (int)tileDataComponent.tileType;

            Vector3 pos = tileObject.transform.position;
            Vector3 rot = tileObject.transform.rotation.eulerAngles;

            TileSaveData tileData = new TileSaveData
            {
                id = tilePayload.Count.ToString(),
                exposition_id = expoData.id.ToString(),
                type = tileTypeIndex,
                has_exhibit = tileObject.GetComponent<PlacedTileData>().hasExhibit,
                position = new float[] { pos.x, 0, pos.z },
                rotation = new float[] { rot.x, rot.y, rot.z }
            };
            tilePayload.Add(tileData);
        }

        List<ExhibitLayoutSaveData> exhibitPayload = new List<ExhibitLayoutSaveData>();

        // We iterate through the placed exhibits in the scene and map them to 
        // the available exhibit IDs from the expoData list.
        for (int i = 0; i < placedExhibits.Length; i++)
        {
            // Safety check: The number of placed objects should not exceed the number of available exhibit IDs.
            if (i >= expoData.exhibits.Count)
            {
                Debug.LogError($"Error during SaveLayout: Placed exhibit count ({placedExhibits.Length}) exceeds available exhibit data ({expoData.exhibits.Count}). Aborting payload creation for remaining exhibits.");
                break;
            }

            Vector3 pos = placedExhibits[i].transform.position;

            ExhibitLayoutSaveData exhibitData = new ExhibitLayoutSaveData
            {
                // This assumes the order of placed exhibits matches the order of the exhibit list in expoData
                id = expoData.exhibits[i].id.ToString(),
                position = new float[] { pos.x, 0, pos.z },
                size = expoData.exhibits[i].size,
            };
            exhibitPayload.Add(exhibitData);
        }

        Vector3 playerSpawnPos = Vector3.zero;
        if (playerSpawn != null)
        {
            playerSpawnPos = playerSpawn.transform.position;
        }

        LayoutSavePayload payload = new LayoutSavePayload
        {
            playerSpawn = new float[] { playerSpawnPos.x, playerSpawnPos.y, playerSpawnPos.z },
            tiles = tilePayload,
            exhibits = exhibitPayload
        };


        string jsonData = JsonUtility.ToJson(payload);

        SaveJsonToAssetsFolder(jsonData);

        string endpoint = $"{expoData.id}";

        string sanctumToken = "";

        ServerCommunicator.Instance.PutRequest(endpoint, jsonData, OnLayoutSaveComplete, sanctumToken);
    }

    private void SaveJsonToAssetsFolder(string jsonData)
    {
        // Application.dataPath points to the Assets folder in the Unity Editor
        string path = Path.Combine(Application.dataPath, "layout");

        try
        {
            // Write the JSON string to the file.
            File.WriteAllText(path, jsonData);
            Debug.Log($"\u2705 Layout JSON saved locally to: {path}");

            // NOTE: In the editor, you might need to call AssetDatabase.Refresh()
            // for the new file to show up immediately in the Project window, 
            // but this is not available in builds and can be slow. 
            // The file is physically saved regardless.
        }
        catch (IOException ex)
        {
            Debug.LogError($"\u274C Failed to save local JSON file to Assets: {ex.Message}");
        }
    }

    private void OnLayoutSaveComplete(bool success, string jsonResponse, string error)
    {
        Debug.Log("JSON REPONSE: " + jsonResponse);

        if (success)
        {
            Debug.Log("Layout saved successfully! Server response received");
        }
        else
        {
            Debug.LogError($"Layout save failed: {error}");
        }
    }

    /// <summary>
    /// Returns the maximum number of exhibits allowed for this exposition.
    /// </summary>
    public int GetMaxExhibitCount()
    {
        return maxExhibitCount;
    }

    /// <summary>
    /// Returns the current number of exhibits placed in the scene.
    /// </summary>
    public int GetCurrentExhibitCount()
    {
        return currentExhibitCount;
    }

    /// <summary>
    /// Increments the count of currently placed exhibits.
    /// Called by TilePlacer upon successful placement.
    /// </summary>
    public void IncrementExhibitCount()
    {
        currentExhibitCount++;
    }

    /// <summary>
    /// Decrements the count of currently placed exhibits.
    /// Called by TilePlacer upon successful deletion.
    /// </summary>
    public void DecrementExhibitCount()
    {
        currentExhibitCount = Mathf.Max(0, currentExhibitCount - 1);
    }
}