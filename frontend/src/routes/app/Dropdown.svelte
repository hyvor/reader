<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';

	interface Props {
		label: string;
		isOpen: boolean;
		onToggle: () => void;
		onClickOutside?: () => void;
		class?: string;
		children: any;
	}

	let { label, isOpen, onToggle, onClickOutside, class: className, children }: Props = $props();
</script>

<div class="dropdown-group">
	<div class="dropdown-container {className || ''}">
		<Button 
			variant="invisible" 
			onclick={onToggle}
			class="dropdown-trigger"
		>
			{label}
			{#snippet end()}
				<IconChevronDown size={16} />
			{/snippet}
		</Button>
		
		{#if isOpen}
			<div class="dropdown-menu">
				{@render children()}
			</div>
		{/if}
	</div>
</div>

<style>
	.dropdown-group {
		position: relative;
	}

	.dropdown-container {
		position: relative;
	}

	:global(.dropdown-trigger) {
		min-width: 180px;
		justify-content: space-between;
	}

	.dropdown-menu {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		background: white;
		border: 1px solid var(--accent-lightest);
		border-radius: 8px;
		box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
		z-index: 1000;
		max-height: 300px;
		overflow-y: auto;
		margin-top: 6px;
		padding: 8px;
	}

	:global(.dropdown-item) {
		width: 100%;
		justify-content: flex-start;
		border-radius: 6px;
		border: none;
		text-align: left;
		padding: 12px 16px;
		margin-bottom: 2px;
		transition: all 0.2s ease;
	}

	:global(.dropdown-item:last-child) {
		margin-bottom: 0;
	}

	:global(.dropdown-item:hover) {
		background: var(--accent-lightest);
		transform: translateY(-1px);
	}
</style> 
