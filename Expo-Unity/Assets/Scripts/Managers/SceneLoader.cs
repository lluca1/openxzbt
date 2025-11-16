using System;
using TMPro;
using UnityEngine;
using UnityEngine.SceneManagement;

public class SceneLoader : MonoBehaviour
{
    public const int SCENE_INDEX_MENU = 1;
    public const int SCENE_INDEX_EXPO = 2;
    public const int SCENE_INDEX_LAYOUT_EDITOR = 3;

    [SerializeField] private GameObject loadingScreen;
    [SerializeField] private TMP_Text progress;

    private static SceneLoader instance;

    private void Awake()
    {
        if (instance == null)
        {
            instance = this;
        }
        else
        {
            Destroy(gameObject);
        }
    }

    private void OnEnable()
    {
        SceneManager.sceneLoaded += OnSceneLoaded;
        InputManager.Controls.Player.Cancel.performed += (ctx) => LoadMenu();
    }

    private void OnDisable()
    {
        SceneManager.sceneLoaded -= OnSceneLoaded;
        InputManager.Controls.Player.Cancel.performed -= (ctx) => LoadMenu();
    }

    private void Start()
    {
        LoadMenu();
    }

    private void OnSceneLoaded(Scene arg0, LoadSceneMode arg1)
    {
        if (arg0.buildIndex == SCENE_INDEX_MENU)
        {
            Cursor.visible = true;
            Cursor.lockState = CursorLockMode.None;
        }
    }

    public void LoadScene(int buildIndex)
    {
        SceneManager.LoadScene(buildIndex);
    }

    public void LoadExpoScene() => LoadScene(SCENE_INDEX_EXPO);

    public void LoadLayoutEditor() => LoadScene(SCENE_INDEX_LAYOUT_EDITOR);

    public void LoadMenu()
    {
        LoadScene(SCENE_INDEX_MENU);

        Cursor.visible = true;
        Cursor.lockState = CursorLockMode.None;

        GameManager.Instance.AmbientController.StopAmbient();
    }

    public void ShowLoadingScreen(bool value)
    {
        loadingScreen.gameObject.SetActive(value);
    }

    public void UpdateProgressText(string text)
    {
        progress.text = text;
    }
}
