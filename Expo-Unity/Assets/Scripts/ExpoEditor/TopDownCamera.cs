using UnityEngine;
using UnityEngine.InputSystem;

public class TopDownCamera : MonoBehaviour
{
    [Header("Movement Settings")]
    [SerializeField] private float moveSpeed = 10f;
    [SerializeField] private float friction = 0.9f;

    [Header("Zoom Settings")]
    [SerializeField] private float zoomSpeed = 5f;
    [SerializeField] private float minZoomHeight = 5f;
    [SerializeField] private float maxZoomHeight = 50f;

    [Header("Input Actions (Assign in Inspector)")]
    // Create and assign these actions in a dedicated Input Action Asset
    [SerializeField] private InputActionReference moveAction;
    [SerializeField] private InputActionReference zoomAction;

    private Vector3 currentVelocity = Vector3.zero;

    private void OnEnable()
    {
        // Enable and start listening for input actions
        if (moveAction != null) moveAction.action.Enable();
        if (zoomAction != null) zoomAction.action.Enable();
    }

    private void OnDisable()
    {
        // Disable actions when the script is not active
        if (moveAction != null) moveAction.action.Disable();
        if (zoomAction != null) zoomAction.action.Disable();
    }

    void Update()
    {
        HandleMovement();
        HandleZoom();
    }

    private void HandleMovement()
    {
        if (moveAction == null) return;

        // 1. Read input vector (from WASD keys)
        Vector2 inputVector = moveAction.action.ReadValue<Vector2>();

        // 2. Calculate acceleration force (only X and Z)
        Vector3 acceleration = new Vector3(inputVector.x, 0, inputVector.y) * moveSpeed;

        // 3. Apply friction to current velocity
        currentVelocity *= Mathf.Pow(friction, Time.deltaTime * 60f);

        // 4. Apply acceleration
        currentVelocity += acceleration * Time.deltaTime;

        // 5. Apply movement
        transform.position += currentVelocity * Time.deltaTime;
    }

    private void HandleZoom()
    {
        if (zoomAction == null) return;

        // 1. Read the scroll wheel input (typically a float)
        float scrollInput = zoomAction.action.ReadValue<float>();

        // Normalize scroll value to be either -1, 0, or 1 for smoother steps
        scrollInput = Mathf.Sign(scrollInput);

        // 2. Calculate the target height change based on input and speed
        float targetHeightChange = scrollInput * zoomSpeed * Time.deltaTime;

        // 3. Determine the new camera position's Y coordinate
        float newY = transform.position.y - targetHeightChange;

        // 4. Clamp the new Y position within the allowed zoom range
        newY = Mathf.Clamp(newY, minZoomHeight, maxZoomHeight);

        // 5. Apply the new position
        transform.position = new Vector3(transform.position.x, newY, transform.position.z);
    }
}