using UnityEngine;
using System;

public class Exhibit : Interactable
{
    [Header("Exhibit Parameters")]
    [SerializeField] private ExhibitData exhibitData;

    private GameObject exhibitModel;
    private ExhibitInspector exhibitInspector;

    // --- New callback for model loading ---
    private Action onAssetLoaded;

    private void Start()
    {
        exhibitInspector = FindFirstObjectByType<ExhibitInspector>();
    }

    private void Setup(GameObject model)
    {
        if (model == null)
        {
            Debug.LogError($"Setup failed: Model data was null for {exhibitData.title}");
            onAssetLoaded?.Invoke(); // Call callback even on failure to avoid eternal loading screen
            return;
        }

        // ... (Model setup logic remains unchanged, which is now correctly ordered) ...
        exhibitModel = Instantiate(model, transform);

        Destroy(model);

        ModelUtility.ScaleToTargetSize(exhibitModel, exhibitData.size);

        exhibitModel.transform.localPosition = Vector3.zero;
        exhibitModel.transform.localRotation = Quaternion.identity;

        for (int i = 0; i < exhibitModel.transform.childCount; i++)
        {
            exhibitModel.transform.GetChild(i).localPosition = Vector3.zero;
            exhibitModel.transform.GetChild(i).localRotation = Quaternion.identity;
        }

        ModelUtility.SetPivotToBottom(exhibitModel);

        Debug.Log($"Exhibit {exhibitData.title} setup complete.");

        onAssetLoaded?.Invoke(); // Signal the model loading is complete
    }

    public override void Interact()
    {
        base.Interact();
        if (exhibitModel != null && exhibitInspector != null)
        {
            exhibitInspector.StartInspect(exhibitModel, exhibitData);
        }
        else
        {
            Debug.LogError("Cannot start inspection: Model or Inspector is null.");
        }
    }

    // --- UPDATED LoadData with Callback ---
    public void LoadData(ExhibitData exhibitData, Action loadedCallback)
    {
        this.exhibitData = exhibitData;
        this.onAssetLoaded = loadedCallback; // Store the callback

        // Pass the internal Setup method to the data loader
        GameManager.Instance.DataLoader.LoadModel(exhibitData.media_path, exhibitData.id.ToString(), Setup);
    }

    public ExhibitData GetExhibitData() => exhibitData;

    public void UpdateScale(float newScale)
    {
        if (exhibitModel != null)
        {
            exhibitData.size = newScale;

            ModelUtility.ScaleToTargetSize(exhibitModel, newScale);

            ModelUtility.SetPivotToBottom(exhibitModel);

            Debug.Log($"Exhibit {exhibitData.title} scale updated to {newScale}.");
        }
        else
        {
            Debug.LogWarning($"Cannot update scale for exhibit {exhibitData.title}: Model not yet loaded or is null.");
        }
    }
}