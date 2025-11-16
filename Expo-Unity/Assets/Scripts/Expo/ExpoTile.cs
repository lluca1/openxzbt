using UnityEngine;

public class ExpoTile : MonoBehaviour
{
    [SerializeField] private Renderer floor, ceiling;
    [SerializeField] private Renderer wallL, wallR, wallB, wallF;
    [SerializeField] private Transform presetModelsParent;
    [SerializeField] private Transform exhibitParent;

    private Material floorTexture, ceilingTexture, wallTexture;

    public float GetSize() => transform.localScale.x * 10;

    // Initialization of materials is often done in Awake/Start or here.
    // We clone the default material so we can modify it without affecting the original asset.
    private void InitMaterials()
    {
        if (floorTexture == null && floor.material != null)
            floorTexture = new Material(floor.material);
        if (ceilingTexture == null && ceiling.material != null)
            ceilingTexture = new Material(ceiling.material);
        if (wallTexture == null && wallL.material != null)
            wallTexture = new Material(wallL.material);
    }

    private void Setup(TileType tileType)
    {
        // Assign the (potentially customized) material instances to the Renderers
        floor.material = floorTexture;
        ceiling.material = ceilingTexture;
        wallL.material = wallTexture;
        wallR.material = wallTexture;
        wallB.material = wallTexture;
        wallF.material = wallTexture;

        /* wall activation logic remains commented out */
    }

    private void LoadFloorTexture(Texture2D texture)
    {
        if (texture != null)
            floorTexture.mainTexture = texture;
    }

    private void LoadCeilingTexture(Texture2D texture)
    {
        if (texture != null)
            ceilingTexture.mainTexture = texture;
    }

    private void LoadWallTexture(Texture2D texture)
    {
        if (texture != null)
            wallTexture.mainTexture = texture;
    }

    // UPDATED IMPLEMENTATION for custom textures
    public void LoadData(TileType tileType, int hasExhibit, ExpoData expoData)
    {
        InitMaterials();

        var dataLoader = GameManager.Instance.DataLoader;

        dataLoader.LoadTexture(expoData.floor_texture, LoadFloorTexture);
        dataLoader.LoadTexture(expoData.ceiling_texture, LoadCeilingTexture);
        dataLoader.LoadTexture(expoData.wall_texture, LoadWallTexture);

        if (hasExhibit == 1)
        {
            exhibitParent.GetChild(0).gameObject.SetActive(true);
        }

        Setup(tileType);
    }

    public void LoadData(TileType tileType, int hasExhibit, ExpoPreset preset, int presetIndex)
    {
        floorTexture = preset.floorTexture;
        ceilingTexture = preset.ceilingTexture;
        wallTexture = preset.wallTexture;

        presetModelsParent.GetChild(presetIndex).gameObject.SetActive(true);

        if (hasExhibit == 1)
        {
            exhibitParent.GetChild(presetIndex).gameObject.SetActive(true);
        }

        Setup(tileType);
    }
}