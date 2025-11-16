using TMPro;
using UnityEngine;
using UnityEngine.InputSystem;
using UnityEngine.UI;

public class ExhibitInspector : MonoBehaviour
{
    [Header("Inspection Setup")]
    [SerializeField] private Camera inspectCamera;
    [SerializeField] private GameObject inspectUI;
    [SerializeField] private TMP_Text modelName, modelDescription;

    [SerializeField, Space] private Vector3 modelSpawnOffset = new Vector3(0, 0, 1.5f);
    [SerializeField] private float rotationSpeed = 2f;

    private GameObject modelInstance;
    private Interactable modelInteractable;
    private FirstPersonController player;
    private Vector3 lastMousePosition;
    private bool isInspecting = false;

    private void Awake()
    {
        inspectCamera.enabled = false;
        inspectUI.SetActive(false);
    }

    private void Update()
    {
        if (isInspecting && modelInstance != null)
        {
            HandleModelRotation();
        }
    }

    private void HandleModelRotation()
    {
        Mouse mouse = Mouse.current;
        if (mouse == null)
        {
            return;
        }

        if (mouse.leftButton.isPressed)
        {
            Vector3 currentMousePosition = mouse.position.ReadValue();

            if (mouse.leftButton.wasPressedThisFrame)
            {
                lastMousePosition = currentMousePosition;
                return;
            }

            Vector3 delta = currentMousePosition - lastMousePosition;

            float rotationX = -delta.y * rotationSpeed * Time.deltaTime;
            float rotationY = delta.x * rotationSpeed * Time.deltaTime;

            modelInstance.transform.Rotate(Vector3.up, rotationY, Space.World);
            modelInstance.transform.Rotate(Vector3.right, rotationX, Space.Self);

            lastMousePosition = currentMousePosition;
        }
    }

    public void Like()
    {

    }

    public void EndInspect()
    {
        isInspecting = false;
        Destroy(modelInstance);
        inspectCamera.enabled = false;
        inspectUI.SetActive(false);

        Cursor.lockState = CursorLockMode.Locked;
        Cursor.visible = false;

        player.EnableController(true);
        player.EnableCamera(true);

        modelInteractable.SetCanInteract(true);
    }

    public void StartInspect(GameObject model, ExhibitData exhibitData)
    {
        if (player == null)
        {
            player = FindAnyObjectByType<FirstPersonController>();
        }

        player.EnableController(false);
        player.EnableCamera(false);
        inspectCamera.enabled = true;

        Vector3 finalSpawnOffset = new Vector3(modelSpawnOffset.x, modelSpawnOffset.y, modelSpawnOffset.z * exhibitData.size);
        Vector3 pos = inspectCamera.transform.position + finalSpawnOffset;
        modelInstance = Instantiate(model, pos, Quaternion.identity);

        for (int i = 0; i < modelInstance.transform.childCount; i++)
        {
            modelInstance.transform.GetChild(i).localPosition = Vector3.zero;
            modelInstance.transform.GetChild(i).localRotation = Quaternion.identity;
        }

        ModelUtility.CenterPivot(modelInstance);

        modelInstance.transform.position = pos;

        modelInteractable = model.transform.parent.GetComponent<Exhibit>();
        modelInteractable.SetCanInteract(false);

        inspectUI.SetActive(true);
        modelName.text = exhibitData.title;
        modelDescription.text = exhibitData.description;

        Cursor.lockState = CursorLockMode.None;
        Cursor.visible = true;

        isInspecting = true;
    }
}