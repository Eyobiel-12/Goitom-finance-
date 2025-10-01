<template>
    <svg 
        :width="size" 
        :height="size" 
        viewBox="0 0 100 100" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
        class="zemen-logo"
    >
        <!-- Background Circle -->
        <circle cx="50" cy="50" r="48" :fill="backgroundColor" stroke="#DC2626" stroke-width="2"/>
        
        <!-- Z Letter -->
        <path 
            d="M25 30 L75 30 L75 40 L35 40 L35 60 L75 60 L75 70 L25 70 Z" 
            :fill="textColor"
        />
        
        <!-- Decorative Elements -->
        <circle cx="20" cy="20" r="3" :fill="accentColor" opacity="0.7"/>
        <circle cx="80" cy="20" r="2" :fill="accentColor" opacity="0.5"/>
        <circle cx="20" cy="80" r="2" :fill="accentColor" opacity="0.5"/>
        <circle cx="80" cy="80" r="3" :fill="accentColor" opacity="0.7"/>
        
        <!-- Inner decorative line -->
        <path d="M30 50 L70 50" stroke="#DC2626" stroke-width="1" opacity="0.3"/>
    </svg>
</template>

<script setup>
defineProps({
    size: {
        type: [String, Number],
        default: 48
    },
    backgroundColor: {
        type: String,
        default: '#FEF2F2'
    },
    textColor: {
        type: String,
        default: '#DC2626'
    },
    accentColor: {
        type: String,
        default: '#DC2626'
    }
})
</script>

<style scoped>
.zemen-logo {
    transition: transform 0.3s ease;
}

.zemen-logo:hover {
    transform: scale(1.05);
}
</style>
