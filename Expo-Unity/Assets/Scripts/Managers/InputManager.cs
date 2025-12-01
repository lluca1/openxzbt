using UnityEngine;
using UnityEngine.InputSystem;

public class InputManager : MonoBehaviour
{
    public static Controls Controls { get; private set; }

    private static InputManager instance;

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

        Controls = new();
    }

    private void OnEnable()
    {
        Controls.Enable();
    }

    private void OnDisable()
    {
        Controls.Disable();
    }

    private void OnDestroy()
    {
        Controls.Disable();
    }

    public void DisableInput()
    {
        Controls.Disable();
    }

    private void OnMouseLock(bool locked)
    {
        Cursor.lockState = locked ? CursorLockMode.Locked : CursorLockMode.None;
        Cursor.visible = !locked;
    }

    public bool IsControllerConnected() => Gamepad.all.Count > 0;
}
