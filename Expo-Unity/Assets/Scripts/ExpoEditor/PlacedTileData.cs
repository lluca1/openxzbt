using UnityEngine;

public class PlacedTileData : MonoBehaviour
{
    public TileType tileType;

    public void Setup(TileType tileType)
    {
        this.tileType = tileType;
    }
}
