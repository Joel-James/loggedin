/**
 * Support tab body.
 *
 * Two `PanelBody` blocks: one with channels for getting help, one
 * with the author's links. Each is rendered as a row of secondary
 * buttons via the local {@see LinkRow} helper.
 *
 * The link arrays are module-level constants — they don't depend on
 * runtime state, so re-deriving them on each render would be wasted
 * work. Editing them only requires a single-file change.
 */
import { __ } from '@wordpress/i18n';
import { Button, Flex, FlexItem, PanelBody } from '@wordpress/components';

/**
 * Channels for getting product help. Each entry maps to a single
 * `<Button>` in the first link row.
 *
 * @type {Array<{ href: string, icon: string, label: string }>}
 */
const SUPPORT_LINKS = [
	{
		href: 'https://docs.foxelabs.com/software/loggedin/',
		icon: 'admin-page',
		label: __( 'Documentation', 'loggedin' ),
	},
	{
		href: 'https://wordpress.org/support/plugin/loggedin/',
		icon: 'groups',
		label: __( 'Support Forums', 'loggedin' ),
	},
	{
		href: 'https://foxelabs.com/contact',
		icon: 'superhero',
		label: __( 'Priority Support', 'loggedin' ),
	},
];

/**
 * Author / project links shown in the second link row.
 *
 * @type {Array<{ href: string, icon: string, label: string }>}
 */
const AUTHOR_LINKS = [
	{
		href: 'https://foxelabs.com/about',
		icon: 'admin-site',
		label: __( 'About Us', 'loggedin' ),
	},
	{
		href: 'https://profiles.wordpress.org/joelcj91/',
		icon: 'wordpress',
		label: __( 'WP.org Profile', 'loggedin' ),
	},
];

/**
 * Horizontal row of secondary buttons rendered from a `links` array.
 *
 * Wraps in a `<Flex wrap>` so long rows reflow on narrow viewports
 * instead of overflowing the panel. Each button opens its link in a
 * new tab — these are all external destinations, and we don't want
 * the user to lose their settings tab state by navigating away.
 *
 * @param {Object} props
 * @param {Array<{ href: string, icon: string, label: string }>} props.links
 *        Link descriptors to render.
 */
const LinkRow = ( { links } ) => (
	<Flex className="loggedin-link-row" gap={ 2 } justify="flex-start" wrap>
		{ links.map( ( link ) => (
			<FlexItem key={ link.href }>
				<Button
					__next40pxDefaultSize
					variant="secondary"
					target="_blank"
					rel="noopener noreferrer"
					icon={ link.icon }
					href={ link.href }
				>
					{ link.label }
				</Button>
			</FlexItem>
		) ) }
	</Flex>
);

/**
 * The tab itself — two panels of links + a paragraph of context.
 */
const Support = () => (
	<>
		<PanelBody
			title={ __( 'Support Information', 'loggedin' ) }
			initialOpen
		>
			<p>
				{ __(
					'Need help with the plugin? Reach out through any of the channels below.',
					'loggedin'
				) }
			</p>
			<LinkRow links={ SUPPORT_LINKS } />
		</PanelBody>

		<PanelBody
			title={ __( 'About the Author', 'loggedin' ) }
			initialOpen
		>
			<p>
				{ __(
					"Hey, I'm Joel James, a Software Engineer based in Kerala, India. I'm passionate about open source and dedicate a lot of my time to contributing to it. If you like this plugin, I'd encourage you to check out my other WordPress plugins as well!",
					'loggedin'
				) }
			</p>
			<LinkRow links={ AUTHOR_LINKS } />
		</PanelBody>
	</>
);

export default Support;
