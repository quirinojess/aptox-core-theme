module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	env: {
		browser: true,
		es2021: true,
	},
	rules: {
		'no-console': 'off',
	},
};
