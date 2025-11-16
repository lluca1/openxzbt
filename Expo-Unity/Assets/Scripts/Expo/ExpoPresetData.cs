using System;
using UnityEngine;

[Serializable]
public struct ExpoPreset
{
    public string name;
    public Color sunColor;
    public Material floorTexture, ceilingTexture, wallTexture;
    public AudioClip ambientTrack;
}

[CreateAssetMenu(fileName = "Expo Presets Settings", menuName = "Expo Data/Expo Presets Settings")]
public class ExpoPresetData : ScriptableObject
{
    public ExpoPreset[] Presets;
}
