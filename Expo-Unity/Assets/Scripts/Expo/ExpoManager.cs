using System.Collections.Generic;
using Unity.VisualScripting;
using UnityEngine;
using UnityEngine.SceneManagement;
using System.Collections;
using System; // Required for Action/delegates

public class ExpoManager : MonoBehaviour
{
    [SerializeField] private ExpoPresetData presetsData;

    [SerializeField] private FirstPersonController playerPrefab;
    [SerializeField] private ExpoTile tileI, tileII, tileL, tileU;
    [SerializeField] private Exhibit exhibitPrefab;

    [SerializeField] private Vector3 playerSpawnOffset;
    [SerializeField] private Vector3 exhibitSpawnOffset;

    [Header("Debug")]
    [SerializeField] private ExpoData expoData;

    [Header("Runtime Settings")]
    [SerializeField] private float updateInterval = 5f;

    private List<ExpoTile> createdTiles = new();
    private List<Exhibit> createdExhibits = new();

    private static ExpoManager instance;

    // Loading State Variables
    private int assetsToLoadCount = 0;
    private int assetsLoadedCount = 0;

    public ExpoData GetExpoData() => expoData;

    private void Awake()
    {
        if (instance == null)
        {
            instance = this;
            // IMPORTANT: Ensure this object persists across scenes if it handles the loading process
            // DontDestroyOnLoad(gameObject);
        }
        else
        {
            Destroy(gameObject);
        }
    }

    private void OnEnable()
    {
        SceneManager.sceneLoaded += OnSceneLoaded;
    }

    private void OnDisable()
    {
        SceneManager.sceneLoaded -= OnSceneLoaded;
        StopAllCoroutines();
    }

    private void OnSceneLoaded(Scene scene, LoadSceneMode loadScene)
    {
        if (scene.buildIndex == SceneLoader.SCENE_INDEX_EXPO)
        {
            // Activate loading screen before starting asset loading
            GameManager.Instance.SceneLoader.ShowLoadingScreen(true);
            StartCoroutine(LoadingCoroutine());
            StartCoroutine(UpdateExhibitDataPeriodically());
        }
    }

    // --- NEW LOADING COROUTINE ---
    private IEnumerator LoadingCoroutine()
    {
        Debug.Log("Starting asset loading process...");

        // 1. Initial Frame Wait (optional, ensures UI is updated)
        yield return null;

        // 2. Count all assets that need to be loaded asynchronously
        assetsToLoadCount = 0;
        assetsLoadedCount = 0;

        // Assets = Tiles (1 floor, 1 ceiling, 1 wall texture per tile) + Exhibits (1 model per exhibit) + 1 Ambient Track
        if (expoData.tiles != null)
            assetsToLoadCount += expoData.tiles.Count * 3;

        if (expoData.exhibits != null)
            assetsToLoadCount += expoData.exhibits.Count;

        if (expoData.preset_theme == -1)
            assetsToLoadCount++; // Custom ambient track

        Debug.Log($"Total assets to load: {assetsToLoadCount}");

        // 3. Execute asset creation and loading
        CreateExpo();

        // 4. Wait until all assets have signaled completion
        while (assetsLoadedCount < assetsToLoadCount - 1)
        {
            GameManager.Instance.SceneLoader.UpdateProgressText($"Assets loaded: {assetsLoadedCount}/{assetsToLoadCount}");
            yield return null;
        }

        // 5. Loading is complete
        Debug.Log("All assets loaded successfully. Disabling loading screen.");
        GameManager.Instance.SceneLoader.ShowLoadingScreen(false);
    }

    // --- NEW LOADING CALLBACK ---
    public void AssetLoadedCallback()
    {
        assetsLoadedCount++;
        Debug.Log($"Asset Loaded: {assetsLoadedCount}/{assetsToLoadCount}");
    }

    private IEnumerator UpdateExhibitDataPeriodically()
    {
        while (true)
        {
            yield return new WaitForSeconds(updateInterval);
            LoadLatestExhibitData();
        }
    }

    private void LoadLatestExhibitData()
    {
        if (expoData != null)
        {
            GameManager.Instance.DataLoader.LoadExpoData(expoData.id.ToString(), ReceiveUpdatedExpoData);
        }
    }

    private void ReceiveUpdatedExpoData(ExpoData newExpoData)
    {
        this.expoData = newExpoData;
        UpdateExhibitScalesDynamically();
    }

    private void PlayAmbientTrack(AudioClip audioClip)
    {
        GameManager.Instance.AmbientController.PlayAmbient(audioClip);
        AssetLoadedCallback(); // Signal ambient track loaded
    }

    private void LoadExpo(ExpoData expoData)
    {
        this.expoData = expoData;

        GameManager.Instance.SceneLoader.LoadExpoScene();
    }

    private void CreateExpo()
    {
        int presetTheme = expoData.preset_theme;

        if (presetTheme != -1)
        {
            Light sun = FindFirstObjectByType<Light>();
            if (sun != null)
                sun.color = presetsData.Presets[presetTheme].sunColor;

            GameManager.Instance.AmbientController.PlayAmbient(presetsData.Presets[presetTheme].ambientTrack);

            // Assets loaded count must be incremented for presets if textures are assigned instantly.
            // Assuming 3 textures + 1 ambient track per tile are handled instantly by preset logic:
            if (expoData.tiles != null)
                assetsLoadedCount += expoData.tiles.Count * 3;
            // Presets don't need a separate callback for ambient track since it's loaded instantly.
        }
        else
        {
            // Custom ambient track loading uses a callback (PlayAmbientTrack) which calls AssetLoadedCallback()
            GameManager.Instance.DataLoader.LoadAudio(expoData.ambient_track, PlayAmbientTrack);
        }

        if (expoData.tiles != null)
        {
            foreach (TileData tile in expoData.tiles)
            {
                TileType tileType = (TileType)tile.type;
                ExpoTile spawnTile = null;

                // ... (Tile Type Switch Statement remains unchanged) ...
                switch (tileType)
                {
                    case TileType.Empty:
                        spawnTile = null;
                        break;
                    case TileType.I:
                        spawnTile = tileI;
                        break;
                    case TileType.II:
                        spawnTile = tileII;
                        break;
                    case TileType.L:
                        spawnTile = tileL;
                        break;
                    case TileType.U:
                        spawnTile = tileU;
                        break;
                }

                if (spawnTile == null)
                {
                    // If TileType is Empty and we counted assets for it, decrement the count.
                    // Assuming TileType.Empty tiles don't have textures to load.
                    if (presetTheme != -1 && tileType == TileType.Empty) assetsLoadedCount -= 3;

                    // If tile is null and it's a custom theme, we need to subtract the expected loads
                    if (presetTheme == -1 && tileType == TileType.Empty) assetsToLoadCount -= 3;

                    continue;
                }

                ExpoTile newTile = Instantiate(spawnTile, tile.GetPosition(), Quaternion.identity);

                if (newTile.transform.childCount > 0)
                {
                    // Assuming GetChild(0) is the tile mesh/visual wrapper
                    newTile.transform.GetChild(0).eulerAngles = tile.GetRotation();
                }

                if (presetTheme != -1)
                {
                    newTile.LoadData(tileType, tile.has_exhibit, presetsData.Presets[presetTheme], presetTheme);
                }
                else
                {
                    // Pass the AssetLoadedCallback to the tile for custom textures
                    newTile.LoadData(tileType, tile.has_exhibit, expoData, AssetLoadedCallback);
                }

                createdTiles.Add(newTile);
            }
        }

        if (expoData.exhibits != null)
        {
            foreach (ExhibitData exhibitData in expoData.exhibits)
            {
                Vector3 tilePos = exhibitData.GetPosition();
                Vector3 pos = tilePos + exhibitSpawnOffset;

                Exhibit exhibit = Instantiate(exhibitPrefab, pos, Quaternion.identity);

                // Pass the AssetLoadedCallback to the exhibit for the 3D model
                exhibit.LoadData(exhibitData, AssetLoadedCallback);

                createdExhibits.Add(exhibit);
            }
        }
        else
        {
            // If there are no exhibits, adjust the asset count
            assetsToLoadCount -= expoData.exhibits.Count;
        }


        // Load Player Spawn (Does not involve async loading, so no change needed)
        Vector3 spawnPos = expoData.GetSpawnpointPosition();
        if (playerPrefab != null) // Check player prefab exists
        {
            Instantiate(playerPrefab, spawnPos + playerSpawnOffset, Quaternion.identity);
        }
    }

    // ... (rest of the methods remain unchanged) ...
    private void UpdateExhibitScalesDynamically()
    {
        if (expoData == null || expoData.exhibits == null || createdExhibits.Count == 0)
        {
            return;
        }

        for (int i = 0; i < createdExhibits.Count; i++)
        {
            if (i < expoData.exhibits.Count && createdExhibits[i] != null)
            {
                Exhibit exhibit = createdExhibits[i];
                ExhibitData data = expoData.exhibits[i];

                float newScale = data.size;

                // Check if the new size from the database is different from the exhibit's current size data.
                if (!Mathf.Approximately(exhibit.GetExhibitData().size, newScale))
                {
                    // Update the scale using the new public method in the Exhibit class.
                    exhibit.UpdateScale(newScale);

                    // Recalculate and update the exhibit's position. This is crucial if scaling affects the model's pivot/offset.
                    Vector3 tilePos = data.GetPosition();

                    Vector3 pos = new Vector3(
                        tilePos.x + exhibitSpawnOffset.x,
                        tilePos.y + exhibitSpawnOffset.y,
                        tilePos.z + exhibitSpawnOffset.z
                    );
                    exhibit.transform.position = pos;
                }
            }
        }
    }

    public void StartLoadExpo(string id)
    {
        GameManager.Instance.DataLoader.LoadExpoData(id, LoadExpo);
    }
}