/** @type {import('tailwindcss').Config} */
export default {
	content: [
		"./resources/**/*.blade.php",
		"./resources/**/*.js",
		"./resources/**/*.vue",
	],
	theme: {
		extend: {
			colors: {
				// Background Colors
				'main': '#0A0A0A',         // Very dark background for the main page
				'layer': '#1A1A1A',        // Slightly lighter for content sections
				'layer-light': '#242424',  // Lightest layer, for subtle distinction
				'input': '#2A2A2A',        // Dark gray for input backgrounds
				
				// Border Colors
				'main-border': '#0e0e0eb3',    // Soft border color for containers and sections
				'layer-border': '#333333',    // Soft border color for containers and sections
				'input-border': '#4E4E4E',    // Neutral gray for input borders
				'input-border-focus': '#6B7280',    // Neutral gray for input borders

				// Text Colors
				'primary': '#F5F5F5',    // White smoke for main text
				'secondary': '#B0B0B0',  // Soft gray for secondary text
				'helper': '#757575',     // Lighter gray for helper text

				// Accent Color
				'accent': '#FF5722',          // Fiery orange for highlights and call-to-actions

				// Button, Text, and Border Colors for Tailwind Shades with Hover
				// Blue
				'blue': '#1E40AF',     // Dark blue button background
				'blue-hover': '#1E3A8A', // Hover color for blue button
				'text-blue': '#3B82F6',       // Tailwind Blue 500 for text
				'border-blue': '#2563EB',     // Tailwind Blue 600 for borders
				'border-blue-hover': '#1D4ED8', // Hover border color for blue button

				// Red
				'red': '#DC2626',      // Dark red button background
				'red-hover': '#B91C1C', // Hover color for red button
				'text-red': '#EF4444',        // Tailwind Red 500 for text
				'border-red': '#B91C1C',      // Tailwind Red 600 for borders
				'border-red-hover': '#991B1B', // Hover border color for red button

				// Green
				'green': '#15803D',    // Dark green button background
				'green-hover': '#166534', // Hover color for green button
				'text-green': '#22C55E',      // Tailwind Green 500 for text
				'border-green': '#16A34A',    // Tailwind Green 600 for borders
				'border-green-hover': '#15803D', // Hover border color for green button

				// Orange
				'orange': '#F97316',   // Slightly lighter than accent for button background
				'orange-hover': '#EA580C', // Hover color for orange button
				'text-orange': '#FB923C',     // Tailwind Orange 500 for text
				'border-orange': '#F97316',   // Tailwind Orange 600 for borders
				'border-orange-hover': '#EA580C', // Hover border color for orange button
			},
		},
	},
	plugins: [],
}

