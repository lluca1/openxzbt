using System.Collections.Generic;
using Unity.VisualScripting;
using UnityEngine;
using UnityEngine.SceneManagement;
using System.Collections;

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
        StopAllCoroutines();
    }

    private void OnSceneLoaded(Scene scene, LoadSceneMode loadScene)
    {
        if (scene.buildIndex == SceneLoader.SCENE_INDEX_EXPO)
        {
            CreateExpo();
            StartCoroutine(UpdateExhibitDataPeriodically());
        }
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

            GameManager.Instance.AmbientController.PlayAmbient(presetsData.Presets[presetTheme].ambientTrack);
        }
        else
        {
            GameManager.Instance.DataLoader.LoadAudio(expoData.ambient_track, PlayAmbientTrack);
        }

        foreach (TileData tile in expoData.tiles)
        {
            TileType tileType = (TileType)tile.type;
            ExpoTile spawnTile = null;

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
                continue;
            }

            ExpoTile newTile = Instantiate(spawnTile, tile.GetPosition(), Quaternion.identity);

            newTile.transform.GetChild(0).eulerAngles = tile.GetRotation();

            if (presetTheme != -1)
            {
                newTile.LoadData(tileType, tile.has_exhibit, presetsData.Presets[presetTheme], presetTheme);
            }
            else
            {
                newTile.LoadData(tileType, tile.has_exhibit, expoData);
            }

            createdTiles.Add(newTile);
        }

        foreach (ExhibitData exhibitData in expoData.exhibits)
        {
            float size = exhibitData.size;
            Vector3 tilePos = exhibitData.GetPosition();

            Vector3 pos = new Vector3(
                tilePos.x + exhibitSpawnOffset.x,
                tilePos.y + exhibitSpawnOffset.y,
                tilePos.z + exhibitSpawnOffset.z
            );

            Exhibit exhibit = Instantiate(exhibitPrefab, pos, Quaternion.identity);

            exhibit.LoadData(exhibitData);

            createdExhibits.Add(exhibit);
        }

        Vector3 spawnPos = expoData.GetSpawnpointPosition();
        Instantiate(playerPrefab, spawnPos + playerSpawnOffset, Quaternion.identity);
    }

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