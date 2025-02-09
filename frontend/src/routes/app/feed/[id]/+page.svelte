<script>
	import { page } from '$app/stores';
	import ItemsView from '../../ItemsView/ItemsView.svelte';
</script>

<ItemsView type="feed" feedId={Number($page.params.id)} />
