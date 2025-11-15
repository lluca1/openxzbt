using System.Collections.Generic;
using UnityEngine;
using System;

[Serializable]
public class ExpoRootData
{
    public ExpoData data;
}


[Serializable]
public class CuratorData
{
    public int id;
    public string name;
    public string email;
}

[Serializable]
public class TileData
{
    public string tileId;
    public int type;
    public float[] position;
    public float[] rotation;

    public Vector3 GetPosition()
    {
        if (position != null && position.Length >= 3)
            return new Vector3(position[0], position[1], position[2]);
        return Vector3.zero;
    }

    public Vector3 GetRotation()
    {
        if (rotation != null && rotation.Length >= 3)
            return new Vector3(rotation[0], rotation[1], rotation[2]);
        return Vector3.zero;
    }
}

[Serializable]
public class ExhibitData
{
    public int id;
    public string title;
    public string description;
    public string media_path;
    public float[] layout_position;
    public int size;

    public Vector3 GetPosition()
    {
        if (layout_position != null && layout_position.Length >= 3)
            return new Vector3(layout_position[0], layout_position[1], layout_position[2]);
        return Vector3.zero;
    }
}

[Serializable]
public class MetaData
{
    public int exhibits_count;
}

[Serializable]
public class ExpoData
{
    public int id;
    public string title;
    public string description;
    public int preset_theme;
    public float[] spawnpoint;

    public CuratorData curator;

    public List<ExhibitData> exhibits;
    public List<TileData> tiles;

    public string floor_texture;
    public string ceiling_texture;
    public string wall_texture;
    public string ambient_track;

    public MetaData meta;

    public Vector3 GetSpawnpointPosition()
    {
        if (spawnpoint != null && spawnpoint.Length >= 3)
            return new Vector3(spawnpoint[0], spawnpoint[1], spawnpoint[2]);
        return Vector3.zero;
    }
}