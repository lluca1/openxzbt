using UnityEngine;
using UnityEngine.InputSystem;
using UnityEngine.EventSystems;
using System.Collections.Generic;

public enum TileType
{
    Empty,
    I,
    II,
    L,
    U,
}

public class TilePlacer : MonoBehaviour
{
    [SerializeField] private GameObject empty, I, II, U, L;

    [Header("Placement Objects")]
    [SerializeField] private GameObject exhibitPrefab;
    [SerializeField] private GameObject playerSpawnPrefab;

    [Header("Input and Placement")]
    [SerializeField] private LayerMask placementMask;
    [SerializeField] private LayerMask deletionMask;

    [Header("Exhibit Settings")]
    [SerializeField] private float exhibitHeightOffset = 2.0f;

    [Header("Spawn Point Settings")]
    [SerializeField] private float spawnHeightOffset = 1.0f;

    [Header("Grid Settings")]
    [SerializeField] private float gridSize = 15f;

    private GameObject currentPrefab;
    private GameObject previewTile;
    private float currentRotation = 0f;

    private bool isPlacingExhibit = false;
    private bool isPlancingSpawn = false;

    private GameObject currentSpawnPoint;

    private Plane placementPlane;
    private GameObject initialTile;

    private void Awake()
    {
        placementPlane = new Plane(Vector3.up, Vector3.zero);
    }

    private void OnEnable()
    {
        // ASSUMPTION: InputManager.Controls is available
        InputManager.Controls.UI.Click.performed += OnPlaceAction;
        InputManager.Controls.UI.RightClick.performed += CancelOrDelete;
        InputManager.Controls.Player.Rotate.performed += OnRotateAction;
    }

    private void OnDisable()
    {
        InputManager.Controls.UI.Click.performed -= OnPlaceAction;
        InputManager.Controls.UI.RightClick.performed -= CancelOrDelete;
        InputManager.Controls.Player.Rotate.performed -= OnRotateAction;
    }

    private void Update()
    {
        if (currentPrefab != null)
        {
            if (Mouse.current == null) return;
            Vector2 screenPosition = Mouse.current.position.ReadValue();

            if (TryGetGridPosition(screenPosition, out Vector3 snappedPosition))
            {
                Quaternion rotation = Quaternion.Euler(0f, currentRotation, 0f);


                if (isPlacingExhibit)
                {
                    snappedPosition += Vector3.up * exhibitHeightOffset;
                }
                else if (isPlancingSpawn)
                {
                    snappedPosition += Vector3.up * spawnHeightOffset;
                }

                if (previewTile == null)
                {
                    previewTile = Instantiate(currentPrefab, snappedPosition, rotation);
                }
                else
                {
                    previewTile.transform.position = snappedPosition;
                    previewTile.transform.rotation = rotation;
                }
            }
        }
    }

    public void OnPlaceAction(InputAction.CallbackContext context)
    {
        if (context.performed)
        {
            if (EventSystem.current.IsPointerOverGameObject())
            {
                Debug.Log("Placement aborted: Mouse is hovering over UI.");
                return;
            }

            if (currentPrefab != null && previewTile != null)
            {
                Vector3 validationPosition = previewTile.transform.position;

                if (!isPlacingExhibit && !isPlancingSpawn)
                {
                    validationPosition.y = 0f;
                }

                if (isPlacingExhibit || isPlancingSpawn)
                {

                    if (IsPositionOccupiedByTile(new Vector3(validationPosition.x, 0f, validationPosition.z))
                        && !IsPositionOccupiedByTopLayer(new Vector3(validationPosition.x, 0f, validationPosition.z)))
                    {

                        if (isPlancingSpawn && currentSpawnPoint != null)
                        {
                            // This internal check is kept from original code
                        }

                        FinalizePlacement();
                    }
                    else if (!IsPositionOccupiedByTile(new Vector3(validationPosition.x, 0f, validationPosition.z)))
                    {
                        Debug.Log("Placement failed: Must be placed on an existing tile.");
                    }
                    else
                    {
                        if (isPlancingSpawn && currentSpawnPoint != null && IsPositionOccupiedByTopLayer(new Vector3(validationPosition.x, 0f, validationPosition.z)) && currentSpawnPoint.transform.position.x == validationPosition.x && currentSpawnPoint.transform.position.z == validationPosition.z)
                        {
                            // Logic to allow placement if moving the existing spawn point
                            FinalizePlacement();
                        }
                        else
                        {
                            Debug.Log("Placement failed: An exhibit or spawn already exists here.");
                        }
                    }
                }
                else
                {
                    if (!IsPositionOccupiedByAny(validationPosition))
                    {
                        FinalizePlacement();
                    }
                    else
                    {
                        Debug.Log("Tile placement failed: The space is occupied.");
                    }
                }
            }
        }
    }

    public void OnRotateAction(InputAction.CallbackContext context)
    {
        if (context.performed && currentPrefab != null)
        {
            if (EventSystem.current.IsPointerOverGameObject())
            {
                Debug.Log("Rotation aborted: Mouse is hovering over UI.");
                return;
            }

            currentRotation += 90f;

            if (currentRotation >= 360f)
            {
                currentRotation -= 360f;
            }

            if (previewTile != null)
            {
                previewTile.transform.rotation = Quaternion.Euler(0f, currentRotation, 0f);
            }

            Debug.Log($"Object rotated to {currentRotation} degrees.");
        }
    }

    public void CancelOrDelete(InputAction.CallbackContext context)
    {
        if (context.performed)
        {
            if (EventSystem.current.IsPointerOverGameObject())
            {
                Debug.Log("Action aborted: Mouse is hovering over UI.");
                return;
            }


            if (currentPrefab != null)
            {
                if (previewTile != null)
                {
                    Destroy(previewTile);
                    previewTile = null;
                    Debug.Log("Placement preview destroyed. Placement cancelled.");
                }

                currentPrefab = null;
                currentRotation = 0f;
                isPlacingExhibit = false;
                isPlancingSpawn = false;
            }

            else
            {
                TryDeleteObjectAtCursor();
            }
        }
    }

    private void TryDeleteObjectAtCursor()
    {
        Vector2 screenPosition = Mouse.current.position.ReadValue();
        Ray ray = Camera.main.ScreenPointToRay(screenPosition);
        RaycastHit hit;

        if (Physics.Raycast(ray, out hit, 1000f, deletionMask))
        {
            GameObject hitObject = hit.collider.gameObject;
            Vector3 hitPosition = hitObject.transform.position; // Store position before destruction

            if (hitObject == initialTile)
            {
                Debug.Log("Cannot destroy the initial base tile.");
                return;
            }

            // 1. DIRECT CLICK on Exhibit or Spawn
            if (hitObject.CompareTag("PlacedExhibit") || hitObject.CompareTag("PlayerSpawn"))
            {
                PlacedTileData tileUnderneath = GetTileAtPosition(hitPosition);

                if (hitObject.CompareTag("PlacedExhibit"))
                {
                    if (tileUnderneath != null)
                    {
                        tileUnderneath.hasExhibit = 0;
                    }
                    // NEW: Decrement the exhibit count
                    GameManager.Instance.ExpoLayoutEditor.DecrementExhibitCount();
                }

                if (hitObject.CompareTag("PlayerSpawn"))
                {
                    if (hitObject == currentSpawnPoint)
                    {
                        currentSpawnPoint = null;
                        Debug.Log("Player Spawn reference cleared.");
                    }
                }

                Destroy(hitObject);
                Debug.Log($"Destroyed placed {hitObject.tag} at: {hitObject.transform.position}");
                return;
            }


            // 2. CLICK on PlacedTile
            else if (hitObject.CompareTag("PlacedTile"))
            {
                Vector3 tilePosition = hitObject.transform.position;

                // Check for and delete the top layer object if present
                GameObject topLayerObject = FindTopLayerObjectAtPosition(tilePosition);

                if (topLayerObject != null)
                {
                    // A top-layer object exists, so we delete it instead of the tile.
                    Debug.Log("Tile click detected occupied space. Deleting top layer object instead.");

                    // Re-run the deletion logic for the top-layer object
                    if (topLayerObject.CompareTag("PlacedExhibit"))
                    {
                        PlacedTileData tileUnderneath = GetTileAtPosition(tilePosition);
                        if (tileUnderneath != null)
                        {
                            tileUnderneath.hasExhibit = 0;
                        }
                        // NEW: Decrement the exhibit count
                        GameManager.Instance.ExpoLayoutEditor.DecrementExhibitCount();
                    }
                    else if (topLayerObject.CompareTag("PlayerSpawn"))
                    {
                        if (topLayerObject == currentSpawnPoint)
                        {
                            currentSpawnPoint = null;
                        }
                    }

                    Destroy(topLayerObject);
                    Debug.Log($"Destroyed placed {topLayerObject.tag} at: {topLayerObject.transform.position}");
                    return; // Top layer object deleted, action complete
                }

                // If no top-layer object was found, proceed to delete the tile.
                Destroy(hitObject);
                Debug.Log($"Destroyed placed Tile at: {hitObject.transform.position}");
                return;
            }
        }
        else
        {
            Debug.Log("No deletable object found under cursor.");
        }
    }

    public void SelectSpawnForPlacement()
    {
        if (playerSpawnPrefab == null)
        {
            Debug.LogError("Player Spawn Prefab is not assigned in the Inspector!");
            return;
        }

        ResetPlacementMode();
        currentPrefab = playerSpawnPrefab;
        isPlancingSpawn = true;
        Debug.Log("Selected Player Spawn for placement preview.");
    }

    public void SelectExhibitForPlacement()
    {
        if (exhibitPrefab == null)
        {
            Debug.LogError("Exhibit Prefab is not assigned in the Inspector!");
            return;
        }

        // NEW LOGIC: Check if the maximum number of exhibits has been reached
        var layoutEditor = GameManager.Instance.ExpoLayoutEditor;
        if (layoutEditor.GetCurrentExhibitCount() >= layoutEditor.GetMaxExhibitCount())
        {
            Debug.LogWarning($"Placement failed: You have reached the maximum allowed exhibits ({layoutEditor.GetMaxExhibitCount()}). Delete an existing exhibit to place a new one.");
            return;
        }

        ResetPlacementMode();
        currentPrefab = exhibitPrefab;
        isPlacingExhibit = true;
        Debug.Log("Selected Exhibit for placement preview.");
    }

    public void Place(int tileIndex)
    {
        TileType tileType = (TileType)tileIndex;
        GameObject selectedPrefab;

        switch (tileType)
        {
            case TileType.Empty:
                selectedPrefab = empty;
                break;
            case TileType.I:
                selectedPrefab = I;
                break;
            case TileType.II:
                selectedPrefab = II;
                break;
            case TileType.L:
                selectedPrefab = L;
                break;
            case TileType.U:
                selectedPrefab = U;
                break;
            default:
                Debug.LogError($"Invalid tile index: {tileIndex}");
                return;
        }

        ResetPlacementMode();
        currentPrefab = selectedPrefab;
        Debug.Log($"Selected {tileType} for placement preview.");
    }

    private void ResetPlacementMode()
    {
        if (previewTile != null)
        {
            Destroy(previewTile);
            previewTile = null;
        }

        currentRotation = 0f;
        isPlacingExhibit = false;
        isPlancingSpawn = false;
    }

    private void FinalizePlacement()
    {
        if (previewTile != null)
        {
            Vector3 placementPosition = previewTile.transform.position;
            PlacedTileData tileUnderneath = GetTileAtPosition(placementPosition);

            if (isPlancingSpawn && currentSpawnPoint != null)
            {
                // PlacedTileData oldTile = GetTileAtPosition(currentSpawnPoint.transform.position); 

                Destroy(currentSpawnPoint);
                Debug.Log("Previous Player Spawn destroyed to place new one.");
                currentSpawnPoint = null;
            }

            GameObject newObject = Instantiate(currentPrefab, placementPosition, previewTile.transform.rotation);


            if (isPlacingExhibit)
            {
                newObject.tag = "PlacedExhibit";

                if (tileUnderneath != null)
                {
                    tileUnderneath.hasExhibit = 1;
                }

                // NEW: Increment the exhibit count in the layout editor
                GameManager.Instance.ExpoLayoutEditor.IncrementExhibitCount();

                Debug.Log($"Placed Exhibit at: {newObject.transform.position}");
            }
            else if (isPlancingSpawn)
            {
                newObject.tag = "PlayerSpawn";
                currentSpawnPoint = newObject;

                Debug.Log($"Placed Player Spawn at: {newObject.transform.position}");
            }
            else
            {
                newObject.tag = "PlacedTile";
                PlacedTileData dataComponent = newObject.AddComponent<PlacedTileData>();

                TileType tileType = GetTileTypeFromPrefab(currentPrefab);
                dataComponent.Setup(tileType);

                Debug.Log($"Placed Tile at: {newObject.transform.position}");
            }

            Destroy(previewTile);
            previewTile = null;
        }
    }

    // Helper method to find the tile component underneath a position
    private PlacedTileData GetTileAtPosition(Vector3 position)
    {
        // Search only at the tile level (Y=0)
        Vector3 tilePosition = new Vector3(position.x, 0f, position.z);

        // We use a small radius to ensure we only hit the tile directly underneath
        Collider[] hitColliders = Physics.OverlapSphere(tilePosition, gridSize / 4f, placementMask);

        foreach (Collider col in hitColliders)
        {
            // Check if the collider belongs to a placed tile (tag check)
            if (col.gameObject.CompareTag("PlacedTile"))
            {
                // Check if the component exists before returning
                PlacedTileData tileData = col.gameObject.GetComponent<PlacedTileData>();
                if (tileData != null)
                {
                    return tileData;
                }
            }
        }
        return null;
    }

    // NEW HELPER: Finds the exhibit or spawn object located on top of the tile at this position (Y=0).
    private GameObject FindTopLayerObjectAtPosition(Vector3 position)
    {
        // Search only at the tile level (Y=0)
        Vector3 tilePosition = new Vector3(position.x, 0f, position.z);

        // Use a small sphere overlap to find objects sitting at the same grid coordinate
        Collider[] hitColliders = Physics.OverlapSphere(tilePosition, gridSize / 4f, deletionMask);

        foreach (Collider col in hitColliders)
        {
            if (col.gameObject.CompareTag("PlacedExhibit") || col.gameObject.CompareTag("PlayerSpawn"))
            {
                // Ensure we don't return the preview tile itself if it's placed here
                if (col.gameObject != previewTile)
                {
                    return col.gameObject;
                }
            }
        }
        return null;
    }

    private TileType GetTileTypeFromPrefab(GameObject prefab)
    {
        if (prefab == empty) return TileType.Empty;
        if (prefab == I) return TileType.I;
        if (prefab == II) return TileType.II;
        if (prefab == L) return TileType.L;
        if (prefab == U) return TileType.U;
        return TileType.Empty;
    }

    private bool IsPositionOccupiedByAny(Vector3 position)
    {
        Collider[] hitColliders = Physics.OverlapSphere(position, gridSize / 4f, placementMask);

        foreach (Collider col in hitColliders)
        {
            if (col.gameObject != previewTile)
            {
                if (col.gameObject.CompareTag("PlacedTile") || col.gameObject.CompareTag("PlacedExhibit") || col.gameObject.CompareTag("PlayerSpawn"))
                {
                    return true;
                }
            }
        }
        return false;
    }

    private bool IsPositionOccupiedByTile(Vector3 position)
    {
        Collider[] hitColliders = Physics.OverlapSphere(position, gridSize / 4f, placementMask);

        foreach (Collider col in hitColliders)
        {
            if (col.gameObject.CompareTag("PlacedTile") && col.gameObject != previewTile)
            {
                return true;
            }
        }
        return false;
    }

    private bool IsPositionOccupiedByTopLayer(Vector3 position)
    {
        Collider[] hitColliders = Physics.OverlapSphere(position, gridSize / 4f, placementMask);

        foreach (Collider col in hitColliders)
        {
            if (col.gameObject.CompareTag("PlacedExhibit") || col.gameObject.CompareTag("PlayerSpawn"))
            {
                if (col.gameObject != previewTile)
                {
                    return true;
                }
            }
        }
        return false;
    }

    private bool TryGetGridPosition(Vector2 screenPosition, out Vector3 snappedPosition)
    {
        snappedPosition = Vector3.zero;

        Ray ray = Camera.main.ScreenPointToRay(screenPosition);
        float distance;

        if (placementPlane.Raycast(ray, out distance))
        {
            Vector3 rawWorldPosition = ray.GetPoint(distance);

            snappedPosition = new Vector3(
                Mathf.Round(rawWorldPosition.x / gridSize) * gridSize,
                0f,
                Mathf.Round(rawWorldPosition.z / gridSize) * gridSize
            );
            return true;
        }

        return false;
    }

    public GameObject GetTilePrefab(TileType type)
    {
        return type switch
        {
            TileType.Empty => empty,
            TileType.I => I,
            TileType.II => II,
            TileType.L => L,
            TileType.U => U,
            _ => empty,
        };
    }

    public GameObject GetExhibitPrefab() => exhibitPrefab;

    public GameObject GetPlayerSpawnPrefab() => playerSpawnPrefab;

    public void SetCurrentSpawnPoint(GameObject spawnObject)
    {
        currentSpawnPoint = spawnObject;
        Debug.Log("TilePlacer's currentSpawnPoint reference synchronized after load.");
    }

    public void Save()
    {
        GameManager.Instance.ExpoLayoutEditor.SaveLayout();
    }

    public void Exit()
    {
        GameManager.Instance.SceneLoader.LoadMenu();
    }
}