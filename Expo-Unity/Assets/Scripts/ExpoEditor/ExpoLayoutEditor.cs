using UnityEngine;
using System.Collections.Generic;
using UnityEngine.SceneManagement;
using System;
using System.IO;

public class ExpoLayoutEditor : MonoBehaviour
{
    private ExpoData expoData;
    private TilePlacer tilePlacer;

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
        // SceneLoader.SCENE_INDEX_LAYOUT_EDITOR is an assumed constant
        if (scene.buildIndex == SceneLoader.SCENE_INDEX_LAYOUT_EDITOR)
        {
            // The layout is created after the scene loads and expoData is populated
            if (expoData != null)
            {
                CreateLayout();
            }
        }
    }

    private void LoadLayout(ExpoData expoData)
    {
        this.expoData = expoData;
        // This command should trigger OnSceneLoaded once the scene is ready
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

            Debug.Log($"Loaded Tile Type {(TileType)tile.type} at {pos}");
        }

        // 2. Load Exhibits
        foreach (ExhibitData exhibitData in expoData.exhibits)
        {
            GameObject exhibitPrefab = tilePlacer.GetExhibitPrefab();
            Vector3 pos = exhibitData.GetPosition();

            GameObject newExhibit = Instantiate(exhibitPrefab, pos, Quaternion.identity);
            newExhibit.tag = "PlacedExhibit";

            Debug.Log($"Loaded Exhibit at {pos}");
        }

        // 3. Load Player Spawn
        GameObject spawnPrefab = tilePlacer.GetPlayerSpawnPrefab();
        Vector3 spawnPos = expoData.GetSpawnpointPosition();

        GameObject newSpawn = Instantiate(spawnPrefab, spawnPos, Quaternion.identity);
        newSpawn.tag = "PlayerSpawn";

        // VITAL FIX: Synchronize TilePlacer's internal state with the loaded spawn point.
        tilePlacer.SetCurrentSpawnPoint(newSpawn);

        Debug.Log($"Loaded Player Spawn at {spawnPos}");
    }

    public void StartLayoutEditor(string expoId)
    {
        // ASSUMPTION: DataLoader.LoadExpoData's callback signature is Action<ExpoData>
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
                size = 1 // Assuming size 1 if no specific component exists
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

        string endpoint = $"{expoData.id}/layout";

        string sanctumToken = "";
        // Assuming ServerCommunicator is correctly set up
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