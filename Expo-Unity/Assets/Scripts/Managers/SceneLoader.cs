using UnityEngine;
using UnityEngine.SceneManagement;

public class SceneLoader : MonoBehaviour
{
    public const int SCENE_INDEX_EXPO = 1;
    public const int SCENE_INDEX_LAYOUT_EDITOR = 2;

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

    public void LoadScene(int buildIndex)
    {
        SceneManager.LoadScene(buildIndex);
    }

    public void LoadExpoScene() => LoadScene(SCENE_INDEX_EXPO);

    public void LoadLayoutEditor() => LoadScene(SCENE_INDEX_LAYOUT_EDITOR);
}
