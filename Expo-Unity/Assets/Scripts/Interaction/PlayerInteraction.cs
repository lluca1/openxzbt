using UnityEngine;

public class PlayerInteraction : MonoBehaviour
{
    [SerializeField] private float rayDistance = 1.5f;
    [SerializeField] private LayerMask interactableLayer;
    [SerializeField] private InteractionUI interactionUI;

    private Vector3 rayViewportPoint = new Vector3(0.5f, 0.5f, 0f);

    private Interactable focusedInteractable;
    private Camera mainCamera;

    private void OnEnable()
    {
        InputManager.Controls.Player.Inspect.performed += ctx => OnInteract();

        mainCamera = GetComponentInChildren<Camera>();
    }

    private void Update()
    {
        Ray ray = mainCamera.ViewportPointToRay(rayViewportPoint);

        if (Physics.Raycast(ray, out RaycastHit hit, rayDistance, interactableLayer))
        {
            if (hit.collider.TryGetComponent(out Interactable interactable))
            {
                focusedInteractable = interactable;
                focusedInteractable.Focus(interactionUI);
            }
        }
        else if (focusedInteractable != null)
        {
            focusedInteractable.LoseFocus(interactionUI);
            focusedInteractable = null;
        }
    }
    private void OnInteract()
    {
        if (focusedInteractable != null && focusedInteractable.CanInteract)
        {
            focusedInteractable.Interact();
        }
    }

}
