using UnityEngine;
using UnityEngine.SceneManagement;

public class GameManager : MonoBehaviour
{
    public bool IsPaused { get; private set; }

    public ExpoManager ExpoManager { get; private set; }
    public SceneLoader SceneLoader { get; private set; }
    public DataLoader DataLoader { get; private set; }

    public static GameManager Instance;

    private void Awake()
    {
        if (Instance == null)
        {
            Instance = this;
        }
        else
        {
            Destroy(gameObject);
        }

        ExpoManager = GetComponent<ExpoManager>();
        SceneLoader = GetComponent<SceneLoader>();
        DataLoader = GetComponentInChildren<DataLoader>();
    }
}
