using System.Collections;
using UnityEngine;

public class FirstPersonController : MonoBehaviour
{
    [Header("Functional Options")]
    [SerializeField] private bool canJump = true;
    [SerializeField] private bool useHeadbob = true;
    [SerializeField] private bool useFootsteps = true;
    [SerializeField] private bool useBhop = true; // NEW: Enable/Disable Bhop logic

    [Header("Movement Parameters")]
    [SerializeField] private float walkSpeed = 6.0f;
    [SerializeField] private float airControl = 0.5f; // NEW: How much control is available in the air
    [SerializeField] private float airControlSpeed = 0.5f; // NEW: Max speed gained via air control

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
        // Check for InputManager null reference if not defined in this snippet
        if (InputManager.Controls != null)
        {
            InputManager.Controls.Player.Jump.performed += (ctx) => pressingJumpKey = true;
            InputManager.Controls.Player.Jump.canceled += (ctx) => pressingJumpKey = false;
        }
    }

    private void OnDisable()
    {
        if (InputManager.Controls != null)
        {
            InputManager.Controls.Player.Jump.performed -= (ctx) => pressingJumpKey = true;
            InputManager.Controls.Player.Jump.canceled -= (ctx) => pressingJumpKey = false;
        }
        StopAllCoroutines();
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

        if (characterController.isGrounded || !useBhop)
        {
            // --- GROUND MOVEMENT (STANDARD) ---
            currentInput = new Vector2(walkSpeed * moveInput.y, walkSpeed * moveInput.x);

            float moveDirectionY = moveDirection.y;
            moveDirection = (transform.TransformDirection(Vector3.forward) * currentInput.x) +
                (transform.TransformDirection(Vector3.right) * currentInput.y);
            moveDirection.y = moveDirectionY;
        }
        else
        {
            // --- AIR MOVEMENT (BHOP/AIR STRAFING) ---
            AirStrafe(moveInput.y, moveInput.x);
        }
    }

    // NEW: Bhop/Air Strafing Logic
    private void AirStrafe(float forward, float right)
    {
        // Use a smaller air control speed to prevent instant velocity changes
        Vector3 playerForward = transform.TransformDirection(Vector3.forward);
        Vector3 playerRight = transform.TransformDirection(Vector3.right);

        // Calculate the direction of the input in world space
        Vector3 wishDir = playerForward * forward + playerRight * right;
        wishDir.y = 0;
        wishDir.Normalize();

        // Calculate the current horizontal velocity
        Vector3 horizontalVelocity = new Vector3(moveDirection.x, 0, moveDirection.z);

        // Calculate speed projection
        float currentSpeed = Vector3.Dot(horizontalVelocity, wishDir);
        float addSpeed = airControlSpeed - currentSpeed;

        if (addSpeed <= 0) return; // Already moving faster than airControlSpeed

        // Clamp the force applied by airControl
        float airControlForce = Mathf.Min(addSpeed, airControl * Time.deltaTime);

        // Apply the force in the direction of the input
        moveDirection.x += wishDir.x * airControlForce;
        moveDirection.z += wishDir.z * airControlForce;
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

        // Preserve horizontal speed for Bhop
        if (useBhop)
        {
            // When jumping, only reset the Y direction
            Vector3 currentHorizontalVelocity = new Vector3(moveDirection.x, 0, moveDirection.z);
            moveDirection = currentHorizontalVelocity;
        }

        // Apply jump force
        moveDirection.y = jumpForce;

        if (jumpSounds.Length == 0) { return; }

        int rnd = Random.Range(0, jumpSounds.Length);
        jumpAudioSource.clip = jumpSounds[rnd];
        jumpAudioSource.Play();
    }

    private void HandleHeadBob()
    {
        if (!characterController.isGrounded) { return; }

        // Check horizontal movement magnitude
        Vector3 horizontalMove = new Vector3(characterController.velocity.x, 0, characterController.velocity.z);

        if (horizontalMove.magnitude > 0.1f)
        {
            // Base the bob speed on the current velocity magnitude for a better effect
            float speedFactor = horizontalMove.magnitude / walkSpeed;
            timer += Time.deltaTime * (walkBobSpeed * speedFactor);
            playerCamera.transform.localPosition = new Vector3(
                playerCamera.transform.localPosition.x,
                defaultYPos + Mathf.Sin(timer) * (walkBobAmount),
                playerCamera.transform.localPosition.z
            );
        }
        else
        {
            // Smoothly reset head position when stopping
            playerCamera.transform.localPosition = Vector3.Lerp(playerCamera.transform.localPosition,
                new Vector3(playerCamera.transform.localPosition.x, defaultYPos, playerCamera.transform.localPosition.z),
                Time.deltaTime * walkBobSpeed);
        }
    }

    private void HandleFootsteps()
    {
        if (!characterController.isGrounded) { return; }

        // Use CharacterController velocity for more accurate check
        Vector3 horizontalVelocity = new Vector3(characterController.velocity.x, 0, characterController.velocity.z);
        if (horizontalVelocity.magnitude < 0.1f) { return; }

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
            // Adjust step speed based on actual horizontal velocity
            float currentHorizontalSpeed = horizontalVelocity.magnitude;
            float speedRatio = currentHorizontalSpeed / walkSpeed;

            // Set next step time shorter if moving faster
            footstepTimer = baseStepSpeed / speedRatio;
        }
    }

    private void ApplyFinalMovement()
    {
        if (!characterController.isGrounded)
        {
            // Apply gravity
            moveDirection.y -= gravity * Time.deltaTime;

            // NEW: Air friction (optional, but helps keep max speed in check)
            if (useBhop)
            {
                // Simple air friction: scale down horizontal velocity slightly (e.g., 0.999f)
                // moveDirection.x *= 0.999f;
                // moveDirection.z *= 0.999f;
            }
        }
        else if (moveDirection.y < 0)
        {
            // Reset vertical velocity when grounded to prevent gravity build-up
            moveDirection.y = -1f;
        }

        characterController.Move(moveDirection * Time.deltaTime);
    }

    // ... (rest of the methods remain unchanged) ...

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

    // Zoom methods included for completeness but not modified
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
}