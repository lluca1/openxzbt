using UnityEngine;

public class PlacedTileData : MonoBehaviour
{
    public TileType tileType;
    public int hasExhibit;

    public void Setup(TileType tileType)
    {
        this.tileType = tileType;
        this.hasExhibit = 0;
    }
}