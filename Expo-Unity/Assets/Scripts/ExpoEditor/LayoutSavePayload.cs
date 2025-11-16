using System;
using System.Collections.Generic;

[Serializable]
public class LayoutSavePayload
{
    public float[] playerSpawn;
    public List<TileSaveData> tiles;
    public List<ExhibitLayoutSaveData> exhibits;
}

[Serializable]
public class TileSaveData
{
    public string id;
    public int type;
    public float[] position;
    public float[] rotation;
}

[Serializable]
public class ExhibitLayoutSaveData
{
    public string id;
    public float[] position;
    public int size;
}