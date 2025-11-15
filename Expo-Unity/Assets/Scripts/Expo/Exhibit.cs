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

        exhibitModel = Instantiate(model, transform);
        Destroy(model);

        ModelUtility.CenterPivot(exhibitModel);

        for (int i = 0; i < exhibitModel.transform.childCount; i++)
        {
            exhibitModel.transform.GetChild(i).localPosition = Vector3.zero;
        }

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
}