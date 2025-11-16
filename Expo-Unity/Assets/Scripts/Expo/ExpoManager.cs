using System.Collections.Generic;
using UnityEngine;
using UnityEngine.SceneManagement;

public class ExpoManager : MonoBehaviour
{
    [SerializeField] private ExpoPresetData presetsData;

    [SerializeField] private FirstPersonController playerPrefab;
    [SerializeField] private ExpoTile tilePrefab;
    [SerializeField] private Exhibit exhibitPrefab;

    [SerializeField] private Vector3 playerSpawnOffset;
    [SerializeField] private Vector3 tileSpawnOffset;
    [SerializeField] private Vector3 exhibitSpawnOffset;

    [Header("Debug")]
    [SerializeField] private ExpoData expoData;

    private List<ExpoTile> createdTiles = new();
    private List<Exhibit> createdExhibits = new();

    private static ExpoManager instance;

    public ExpoData GetExpoData() => expoData;

    private void Awake()
    {
        if (instance == null)
        {
            instance = this;
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
    }

    private void OnSceneLoaded(Scene scene, LoadSceneMode loadScene)
    {
        if (scene.buildIndex == SceneLoader.SCENE_INDEX_EXPO)
        {
            CreateExpo();
        }
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
            sun.color = presetsData.Presets[presetTheme].sunColor;
        }

        foreach (TileData tile in expoData.tiles)
        {
            ExpoTile newTile = Instantiate(tilePrefab, tile.GetPosition(), Quaternion.identity);

            newTile.transform.eulerAngles = tile.GetRotation();

            TileType tileType = (TileType)tile.type;

            if (presetTheme != -1)
            {
                newTile.LoadData(tileType, presetsData.Presets[presetTheme], presetTheme);
            }
            else
            {
                newTile.LoadData(tileType, expoData.id.ToString());
            }

            createdTiles.Add(newTile);
        }

        // preset layout
        // Vector3 pos = tileSpawnOffset + new Vector3(0, 0, createdTiles.Count * tilePrefab.GetSize())
        // ExpoTile tile = Instantiate(tilePrefab, pos, Quaternion.identity);

        foreach (ExhibitData exhibitData in expoData.exhibits)
        {
            Vector3 pos = exhibitData.GetPosition() + exhibitSpawnOffset;

            Exhibit exhibit = Instantiate(exhibitPrefab, pos, Quaternion.identity);

            exhibit.LoadData(exhibitData);

            createdExhibits.Add(exhibit);
        }

        Vector3 spawnPos = expoData.GetSpawnpointPosition();
        Instantiate(playerPrefab, spawnPos + playerSpawnOffset, Quaternion.identity);
    }

    public void StartLoadExpo(string id)
    {
        GameManager.Instance.DataLoader.LoadExpoData(id, LoadExpo);
    }
}
