using TMPro;
using UnityEngine;
using UnityEngine.UI;

public class InteractionUI : MonoBehaviour
{
    [SerializeField] private TMP_Text interactionMessageText;
    [SerializeField] private Image interactImage;
    [SerializeField] private Sprite ableToInteractIcon, unableToInteractIcon;

    private void Awake()
    {
        HideInterface();
    }

    public void OnInteractableFocus(bool canInteract, string message)
    {
        interactImage.enabled = canInteract ? true : false;
        interactImage.sprite = canInteract ? ableToInteractIcon : unableToInteractIcon;
        interactionMessageText.text = message;
    }

    public void OnInteractableLoseFocus()
    {
        HideInterface();
    }

    private void HideInterface()
    {
        interactImage.enabled = false;
        interactionMessageText.text = string.Empty;
    }
}
