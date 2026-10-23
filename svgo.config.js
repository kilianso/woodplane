export default {
	plugins: [
		{
			// svgo 4 keeps the viewBox by default (removeViewBox is no longer part of preset-default)
			name: 'preset-default',
		},
	],
};
