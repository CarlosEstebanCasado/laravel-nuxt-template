<script setup lang="ts">
const localePath = useLocalePath()

await navigateTo(localePath('/docs/getting-started'), { replace: true })
</script>
