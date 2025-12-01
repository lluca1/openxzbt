using UnityEngine;

public class ModelUtility : MonoBehaviour
{
    public static bool ScaleToTargetSize(GameObject modelInstance, float targetSize)
    {
        if (modelInstance == null || targetSize <= 0)
        {
            Debug.LogError("ModelScaler: Invalid input model or target size.");
            return false;
        }

        Renderer[] renderers = modelInstance.GetComponentsInChildren<Renderer>();

        if (renderers.Length == 0)
        {
            Debug.LogWarning($"ModelScaler: Model '{modelInstance.name}' has no Renderer components for size calculation.");
            return false;
        }

        Bounds combinedBounds = renderers[0].bounds;
        for (int i = 1; i < renderers.Length; i++)
        {
            combinedBounds.Encapsulate(renderers[i].bounds);
        }

        float maxDimension = Mathf.Max(combinedBounds.size.x, combinedBounds.size.y, combinedBounds.size.z);

        float scaleFactor = targetSize / maxDimension;

        modelInstance.transform.localScale *= scaleFactor;

        Debug.Log($"ModelScaler: '{modelInstance.name}' scaled from {maxDimension:F2} to {targetSize:F2} (Factor: {scaleFactor:F4}).");

        return true;
    }

    public static GameObject SetPivotToBottom(GameObject originalModel)
    {
        if (originalModel == null) return null;

        Renderer[] renderers = originalModel.GetComponentsInChildren<Renderer>();

        if (renderers.Length == 0)
        {
            Debug.LogError("Cannot set pivot to bottom: Model does not have a valid Renderer component on itself OR its children.");
            return originalModel;
        }

        // Get the combined bounds in world space
        Bounds combinedBounds = renderers[0].bounds;
        for (int i = 1; i < renderers.Length; i++)
        {
            combinedBounds.Encapsulate(renderers[i].bounds);
        }

        // Calculate the shift needed to move the bottom (min.y) to the model's local origin (Y=0)
        float worldShiftY = originalModel.transform.position.y - combinedBounds.min.y;

        // Apply the shift to the model's transform position
        originalModel.transform.position += Vector3.up * worldShiftY;

        Debug.Log($"Pivot set to bottom for {originalModel.name}. Base shifted to Y=0 in local space.");

        return originalModel;
    }

    public static GameObject CenterPivot(GameObject originalModel)
    {
        if (originalModel == null) return null;

        Renderer[] renderers = originalModel.GetComponentsInChildren<Renderer>();

        if (renderers.Length == 0)
        {
            Debug.LogError("Cannot center pivot: Model does not have a valid Renderer component on itself OR its children.");
            return originalModel;
        }

        // 1. Calculate the combined bounds of all renderers
        Bounds combinedBounds = renderers[0].bounds;
        for (int i = 1; i < renderers.Length; i++)
        {
            combinedBounds.Encapsulate(renderers[i].bounds);
        }

        // 2. Calculate the offset from the model's current transform position to the geometric center
        Vector3 offset = originalModel.transform.position - combinedBounds.center;

        // 3. Shift the model's transform position by this offset
        // This effectively makes the model's transform position the center of the model's geometry.
        originalModel.transform.position -= offset;

        Debug.Log($"Pivot set to center for {originalModel.name}. Centered via transform position.");

        return originalModel;
    }
}