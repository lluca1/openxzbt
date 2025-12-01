using UnityEngine;

public class AmbientController : MonoBehaviour
{
    private AudioSource audioSource;

    private static AmbientController instance;

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

        audioSource = GetComponent<AudioSource>();
    }

    public void PlayAmbient(AudioClip clip)
    {
        audioSource.clip = clip;
        audioSource.Play();
    }

    public void StopAmbient()
    {
        audioSource.Stop();
    }
}
