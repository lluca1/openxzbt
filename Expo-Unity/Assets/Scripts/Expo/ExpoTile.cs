using UnityEngine;

public class ExpoTile : MonoBehaviour
{
    [SerializeField] private Renderer floor, ceiling;
    [SerializeField] private Renderer wallL, wallR, wallB, wallF;
    [SerializeField] private Transform presetModelsParent;

    private Material floorTexture, ceilingTexture, wallTexture;

    public float GetSize() => transform.localScale.x * 10;

    private void Setup(TileType tileType)
    {
        floor.material = floorTexture;
        ceiling.material = ceilingTexture;
        wallL.material = wallTexture;
        wallR.material = wallTexture;
        wallB.material = wallTexture;
        wallF.material = wallTexture;

        wallL.gameObject.SetActive(false);
        wallR.gameObject.SetActive(false);
        wallB.gameObject.SetActive(false);
        wallF.gameObject.SetActive(false);

        switch (tileType)
        {
            case TileType.I:
                wallR.gameObject.SetActive(true);
                break;
            case TileType.II:
                wallL.gameObject.SetActive(true);
                wallR.gameObject.SetActive(true);
                break;
            case TileType.L:
                wallR.gameObject.SetActive(true);
                wallF.gameObject.SetActive(true);
                break;
            case TileType.U:
                wallL.gameObject.SetActive(true);
                wallF.gameObject.SetActive(true);
                wallR.gameObject.SetActive(true);
                break;
        }
    }

    private void LoadFloorTexture(Texture2D texture)
    {
        floorTexture.mainTexture = texture;
    }

    private void LoadCeilingTexture(Texture2D texture)
    {
        ceilingTexture.mainTexture = texture;
    }

    private void LoadWallTexture(Texture2D texture)
    {
        wallTexture.mainTexture = texture;
    }

    public void LoadData(TileType tileType, string expoId)
    {
        var dataLoader = GameManager.Instance.DataLoader;

        dataLoader.LoadTexture(expoId, LoadFloorTexture);
        dataLoader.LoadTexture(expoId, LoadCeilingTexture);
        dataLoader.LoadTexture(expoId, LoadWallTexture);

        Setup(tileType);
    }

    public void LoadData(TileType tileType, ExpoPreset preset, int presetIndex)
    {
        floorTexture = preset.floorTexture;
        ceilingTexture = preset.ceilingTexture;
        wallTexture = preset.wallTexture;

        presetModelsParent.GetChild(presetIndex).gameObject.SetActive(true);

        Setup(tileType);
    }
}