using UnityEngine;
using System;

public class Exhibit : Interactable
{
    [Header("Exhibit Parameters")]
    [SerializeField] private ExhibitData exhibitData;

    private GameObject exhibitModel;
    private ExhibitInspector exhibitInspector;

    private void Start()
    {
        exhibitInspector = FindFirstObjectByType<ExhibitInspector>();
    }

    private void Setup(GameObject model)
    {
        if (model == null)
        {
            Debug.LogError($"Setup failed: Model data was null for {exhibitData.title}");
            return;
        }

        // Instantiate the model as a child and store the reference
        exhibitModel = Instantiate(model, transform);

        // Destroy the original loaded asset, not the instantiated one.
        Destroy(model);

        ModelUtility.SetPivotToBottom(exhibitModel);

        // Reset child local positions
        for (int i = 0; i < exhibitModel.transform.childCount; i++)
        {
            exhibitModel.transform.GetChild(i).localPosition = Vector3.zero;
        }

        // Apply initial scale
        ModelUtility.ScaleToTargetSize(exhibitModel, exhibitData.size);

        Debug.Log($"Exhibit {exhibitData.title} setup complete.");
    }

    public override void Interact()
    {
        base.Interact();
        exhibitInspector.StartInspect(exhibitModel, exhibitData);
    }

    public void LoadData(ExhibitData exhibitData)
    {
        this.exhibitData = exhibitData;
        GameManager.Instance.DataLoader.LoadModel(exhibitData.media_path, exhibitData.id.ToString(), Setup);
    }

    // Public method to allow ExpoManager to retrieve the current data for comparison
    public ExhibitData GetExhibitData() => exhibitData;

    // Public method to update the scale dynamically
    public void UpdateScale(float newScale)
    {
        if (exhibitModel != null)
        {
            // Update the exhibitData size to reflect the new value for future comparisons
            exhibitData.size = newScale;

            // Re-run the utility function to apply the new scaling to the model object.
            // This ensures consistent scaling logic (e.g., pivot correction) is applied 
            // during dynamic updates as well as initial setup.
            ModelUtility.ScaleToTargetSize(exhibitModel, newScale);
            Debug.Log($"Exhibit {exhibitData.title} scale updated to {newScale}.");
        }
        else
        {
            Debug.LogWarning($"Cannot update scale for exhibit {exhibitData.title}: Model not yet loaded or is null.");
        }
    }
}