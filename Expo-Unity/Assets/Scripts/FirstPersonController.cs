using System.Collections;
using UnityEngine;

public class FirstPersonController : MonoBehaviour
{
    [Header("Functional Options")]
    [SerializeField] private bool canJump = true;
    [SerializeField] private bool useHeadbob = true;
    [SerializeField] private bool useFootsteps = true;

    [Header("Movement Parameters")]
    [SerializeField] private float walkSpeed = 6.0f;

    [Header("Look Parameters")]
    [SerializeField] private float mouseSensitivity = 25f;
    [SerializeField] private float lookSmoothTime = 0.05f;
    [SerializeField, Range(1, 100)] private float upperLookLimit = 80.0f;
    [SerializeField, Range(1, 100)] private float lowerLookLimit = 80.0f;

    [Header("Jumping Parameters")]
    [SerializeField] private float jumpForce = 8.0f;
    [SerializeField] private float gravity = 30.0f;

    [Header("Headbob Parameters")]
    [SerializeField] private float walkBobSpeed = 14.0f;
    [SerializeField] private float walkBobAmount = 0.05f;
    private float defaultYPos = 0;
    private float timer;

    [Header("Footstep Parameters")]
    [SerializeField] private float baseStepSpeed = 0.5f;
    private float footstepTimer = 0f;

    [Header("Zoom Parameters")]
    [SerializeField] private float targetZoomFov = 60f;
    [SerializeField] private float timeToZoom = 1f;

    [Header("Audio")]
    [SerializeField] private AudioSource footstepsAudioSource;
    [SerializeField] private AudioSource jumpAudioSource;

    [SerializeField] private AudioClip[] footstepsSounds;
    [SerializeField] private AudioClip[] jumpSounds;

    private Camera playerCamera;
    private CharacterController characterController;

    private Vector3 moveDirection;
    private Vector2 moveInput;
    private Vector2 currentInput;
    private Vector2 currentLookVelocity;
    private Vector2 currentLookInput;

    private float rotationX = 0;

    private bool pressingJumpKey;

    public bool CanMove { get; private set; } = true;

    private bool ShouldJump() => pressingJumpKey && characterController.isGrounded;

    private void Awake()
    {
        playerCamera = GetComponentInChildren<Camera>();
        characterController = GetComponent<CharacterController>();

        defaultYPos = playerCamera.transform.localPosition.y;

        EnableController(true);
    }

    private void Start()
    {
        InputManager.Controls.Player.Jump.performed += (ctx) => pressingJumpKey = true;
        InputManager.Controls.Player.Jump.canceled += (ctx) => pressingJumpKey = false;
        //InputManager.Controls.Player.Zoom.performed += (ctx) => ToggleZoom();
    }

    private void OnDisable()
    {
        InputManager.Controls.Player.Jump.performed -= (ctx) => pressingJumpKey = true;
        InputManager.Controls.Player.Jump.canceled -= (ctx) => pressingJumpKey = false;
        //InputManager.Controls.Player.Zoom.performed -= (ctx) => ToggleZoom();
    }

    private void Update()
    {
        if (!CanMove || GameManager.Instance.IsPaused) { return; }

        HandleMovementInput();
        HandleMouseLook();

        if (canJump) { HandleJump(); }
        if (useHeadbob) { HandleHeadBob(); }
        if (useFootsteps) { HandleFootsteps(); }

        ApplyFinalMovement();
    }

    private void HandleMovementInput()
    {
        moveInput = InputManager.Controls.Player.Move.ReadValue<Vector2>();
        currentInput = new Vector2(walkSpeed * moveInput.y, walkSpeed * moveInput.x);

        float moveDirectionY = moveDirection.y;
        moveDirection = (transform.TransformDirection(Vector3.forward) * currentInput.x) +
            (transform.TransformDirection(Vector3.right) * currentInput.y);
        moveDirection.y = moveDirectionY;
    }

    private void HandleMouseLook()
    {
        Vector2 lookInput = InputManager.Controls.Player.Look.ReadValue<Vector2>();

        currentLookInput = Vector2.SmoothDamp(
                currentLookInput,
                lookInput,
                ref currentLookVelocity, // Passed by reference
                lookSmoothTime
            );


        float mouseX = currentLookInput.x * mouseSensitivity * Time.deltaTime;
        float mouseY = currentLookInput.y * mouseSensitivity * Time.deltaTime;

        rotationX -= mouseY;

        rotationX = Mathf.Clamp(rotationX, -upperLookLimit, lowerLookLimit);
        playerCamera.transform.localRotation = Quaternion.Euler(rotationX, 0, 0);

        transform.Rotate(Vector3.up * mouseX);
    }

    private void HandleJump()
    {
        if (!ShouldJump()) { return; }

        moveDirection.y = jumpForce;

        if (jumpSounds.Length == 0) { return; }

        int rnd = Random.Range(0, jumpSounds.Length);
        jumpAudioSource.clip = jumpSounds[rnd];
        jumpAudioSource.Play();
    }

    private void HandleHeadBob()
    {
        if (!characterController.isGrounded) { return; }

        if (Mathf.Abs(moveDirection.x) > 0.1f || Mathf.Abs(moveDirection.z) > 0.1f)
        {
            timer += Time.deltaTime * (walkBobSpeed);
            playerCamera.transform.localPosition = new Vector3(
                playerCamera.transform.localPosition.x,
                defaultYPos + Mathf.Sin(timer) * (walkBobAmount),
                playerCamera.transform.localPosition.z
                );
        }
    }

    private void HandleFootsteps()
    {
        if (!characterController.isGrounded) { return; }

        if (currentInput == Vector2.zero) { return; }

        if (footstepsSounds.Length == 0) { return; }

        footstepTimer -= Time.deltaTime;

        if (footstepTimer <= 0)
        {
            if (Physics.Raycast(playerCamera.transform.position, Vector3.down, out RaycastHit hit, 3))
            {
                int rnd = Random.Range(0, footstepsSounds.Length);
                footstepsAudioSource.clip = footstepsSounds[rnd];
                footstepsAudioSource.Play();
            }
            footstepTimer = baseStepSpeed * baseStepSpeed;
        }
    }

    private void ApplyFinalMovement()
    {
        if (!characterController.isGrounded)
        {
            moveDirection.y -= gravity * Time.deltaTime;
        }

        characterController.Move(moveDirection * Time.deltaTime);
    }

    private void ToggleZoom()
    {
        StartCoroutine(ZoomCoroutine(targetZoomFov));
    }

    private IEnumerator ZoomCoroutine(float targetFOV)
    {
        float startingFOV = playerCamera.fieldOfView;
        float timeElapsed = 0;

        while (timeElapsed < timeToZoom)
        {
            playerCamera.fieldOfView = Mathf.Lerp(startingFOV, targetFOV, timeElapsed / timeToZoom);
            timeElapsed += Time.deltaTime;
            yield return null;
        }

        playerCamera.fieldOfView = targetFOV;
    }

    public void EnableController(bool value)
    {
        CanMove = value;
        Cursor.lockState = value ? CursorLockMode.Locked : CursorLockMode.None;
        Cursor.visible = !value;
    }

    public void EnableCamera(bool value)
    {
        playerCamera.enabled = value;
    }
}