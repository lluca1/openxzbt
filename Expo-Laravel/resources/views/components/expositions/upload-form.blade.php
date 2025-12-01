<div class="space-y-4">
    <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">upload new exhibit</h2>
    <p class="text-[11px] text-zinc-500">drop the OBJ geometry and its matching MTL so the 3d client can retrieve both later.</p>

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-4">
        <div class="space-y-2">
            <label for="exhibit-title" class="block text-[11px] text-zinc-400">exhibit title</label>
            <input
                id="exhibit-title"
                type="text"
                wire:model.defer="title"
                placeholder="e.g. cute cat sculpture"
                class="w-full bg-[#050608] border border-zinc-700 focus:border-zinc-300 outline-none px-3 py-2 rounded-none text-[12px] text-zinc-100 placeholder:text-zinc-500"
            >
            @error('title')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="exhibit-description" class="block text-[11px] text-zinc-400">description</label>
            <textarea
                id="exhibit-description"
                rows="4"
                wire:model.defer="description"
                placeholder="what is this exhibit about?"
                class="w-full bg-[#050608] border border-zinc-700 focus:border-zinc-300 outline-none px-3 py-2 rounded-none text-[12px] text-zinc-100 placeholder:text-zinc-500"
            ></textarea>
            @error('description')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="model-file" class="block text-[11px] text-zinc-400">3d model (obj)</label>
            <input
                id="model-file"
                type="file"
                wire:model="modelFile"
                accept=".obj"
                class="input-file-fancy"
            >
            @error('modelFile')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
            <div wire:loading wire:target="modelFile" class="text-[10px] text-[#38bdf8]">uploading...</div>
        </div>

        <div class="space-y-2">
            <label for="material-file" class="block text-[11px] text-zinc-400">materials file (mtl)</label>
            <input
                id="material-file"
                type="file"
                wire:model="materialFile"
                accept=".mtl"
                class="input-file-fancy"
            >
            @error('materialFile')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
            <div wire:loading wire:target="materialFile" class="text-[10px] text-[#38bdf8]">uploading...</div>
        </div>

        <div class="space-y-2">
            <label for="texture-files" class="block text-[11px] text-zinc-400">texture files (png, jpg, webp)</label>
            <input
                id="texture-files"
                type="file"
                wire:model="textureFiles"
                multiple
                accept=".png,.jpg,.jpeg,.bmp,.webp"
                class="input-file-fancy"
            >
            @error('textureFiles')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
            @error('textureFiles.*')
                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
            @enderror
            <div wire:loading wire:target="textureFiles" class="text-[10px] text-[#38bdf8]">uploading textures...</div>
        </div>

        <button type="submit" class="w-full px-4 py-2 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717]">
            :: UPLOAD EXHIBIT
        </button>
    </form>
</div>
