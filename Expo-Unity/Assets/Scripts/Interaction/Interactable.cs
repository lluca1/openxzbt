using UnityEngine;
using UnityEngine.Events;

public class Interactable : MonoBehaviour
{
    [SerializeField] protected UnityEvent onInteract;

    [SerializeField] private bool canInteract = true;
    [SerializeField] private bool oneTimeInteraction = false;
    [SerializeField] private bool hideObjectOnInteract = false;

    [SerializeField, Space] private string ableToInteractMessage = "Interact";
    [SerializeField] private string unableToInteractMessage;

    public bool CanInteract => canInteract;

    public void SetCanInteract(bool value) => canInteract = value;

    protected virtual void Awake() { }

    public virtual void Focus(InteractionUI ui)
    {
        string interactMessage = canInteract ? ableToInteractMessage : unableToInteractMessage;
        ui.OnInteractableFocus(canInteract, interactMessage);
    }

    public virtual void LoseFocus(InteractionUI ui)
    {
        ui.OnInteractableLoseFocus();
    }

    public virtual void Interact()
    {
        if (oneTimeInteraction) { SetCanInteract(false); }

        if (hideObjectOnInteract) { gameObject.SetActive(false); }

        onInteract?.Invoke();
    }
}
