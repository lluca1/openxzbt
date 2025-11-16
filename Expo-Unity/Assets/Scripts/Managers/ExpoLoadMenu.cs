using TMPro;
using UnityEngine;
using UnityEngine.UI;

public class ExpoLoadMenu : MonoBehaviour
{
    [SerializeField] private TMP_InputField idField;
    [SerializeField] private Button loadButton;
    [SerializeField] private Button editButton;

    private void OnEnable()
    {
        loadButton.onClick.AddListener(OnLoadClick);
        editButton.onClick.AddListener(OnEditClick);
    }

    private void OnDisable()
    {
        loadButton.onClick.RemoveListener(OnLoadClick);
        editButton.onClick.RemoveListener(OnEditClick);
    }

    private void OnLoadClick()
    {
        string expoId = idField.text;
        GameManager.Instance.ExpoManager.StartLoadExpo(expoId);
    }

    private void OnEditClick()
    {
        string expoId = idField.text;
        GameManager.Instance.ExpoLayoutEditor.StartLayoutEditor(expoId);
    }
}
