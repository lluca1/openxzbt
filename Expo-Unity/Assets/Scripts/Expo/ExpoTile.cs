using UnityEngine;
using System; // Required for Action

public class ExpoTile : MonoBehaviour
{
    [SerializeField] private Renderer floor, ceiling;
    [SerializeField] private Renderer wallL, wallR, wallB, wallF;
    [SerializeField] private Transform presetModelsParent;
    [SerializeField] private Transform exhibitParent;

    [SerializeField] private Material floorMaterial, ceilingMaterial, wallMaterial;
    
    // --- New callback for custom texture loading ---
    private Action onAssetLoaded; 

    public float GetSize() => transform.localScale.x * 10;

    private void LoadFloorTexture(Texture2D texture)
    {
        floorMaterial.mainTexture = texture;
        floor.material = floorMaterial;
        onAssetLoaded?.Invoke(); // Signal completion
    }

    private void LoadCeilingTexture(Texture2D texture)
    {
        ceilingMaterial.mainTexture = texture;
        ceiling.material = ceilingMaterial;
        onAssetLoaded?.Invoke(); // Signal completion
    }

    private void LoadWallTexture(Texture2D texture)
    {
        wallMaterial.mainTexture = texture;
        wallL.material = wallMaterial;
        wallR.material = wallMaterial;
        wallB.material = wallMaterial;
        wallF.material = wallMaterial;
        onAssetLoaded?.Invoke(); // Signal completion
    }

    // --- UPDATED LoadData for Custom Theme ---
    public void LoadData(TileType tileType, int hasExhibit, ExpoData expoData, Action loadedCallback)
    {
        this.onAssetLoaded = loadedCallback; // Store the callback

        var dataLoader = GameManager.Instance.DataLoader;

        // Each of these calls will execute LoadFloorTexture/LoadCeilingTexture/LoadWallTexture 
        // which then call onAssetLoaded. (3 total callbacks per tile)
        dataLoader.LoadTexture(expoData.floor_texture, LoadFloorTexture);
        dataLoader.LoadTexture(expoData.ceiling_texture, LoadCeilingTexture);
        dataLoader.LoadTexture(expoData.wall_texture, LoadWallTexture); 

        if (hasExhibit == 1)
        {
            // IMPORTANT: If you use presetIndex to find the exhibit parent child in the preset version, 
            // you should use a logical index here too, or ensure GetChild(0) is the correct dynamic exhibit point.
            exhibitParent.GetChild(0).gameObject.SetActive(true);
        }
    }

    // LoadData for Preset Theme (no change needed as presets are assumed to load instantly)
    public void LoadData(TileType tileType, int hasExhibit, ExpoPreset preset, int presetIndex)
    {
        // ... (Preset logic remains unchanged) ...
        floorMaterial = preset.floorTexture;
        ceilingMaterial = preset.ceilingTexture;
        wallMaterial = preset.wallTexture;

        floor.material = floorMaterial;
        ceiling.material = ceilingMaterial;
        wallL.material = wallMaterial;
        wallR.material = wallMaterial;
        wallB.material = wallMaterial;
        wallF.material = wallMaterial;

        // Assuming presetModelsParent.GetChild(presetIndex) is the correct model to show
        if (presetModelsParent.childCount > presetIndex)
            presetModelsParent.GetChild(presetIndex).gameObject.SetActive(true);

        if (hasExhibit == 1)
        {
            // Assuming exhibitParent.GetChild(presetIndex) is the correct exhibit stand model
            if (exhibitParent.childCount > presetIndex)
                exhibitParent.GetChild(presetIndex).gameObject.SetActive(true);
        }
    }
}