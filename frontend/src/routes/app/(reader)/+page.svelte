<script lang="ts">
	import { goto } from "$app/navigation";
	import { collections } from "../appStore";

    $effect(() => {
        if ($collections.length > 0) {
            goto(`/app/${$collections[0].slug}`);
        }
    });
</script>
