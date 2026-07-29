/**
 * Admin app root.
 *
 * Mounts the page header (with the tab nav), the active tab body, the
 * sticky footer (Settings tab only), and the snackbar notice list.
 *
 * Tab state is plain `useState` — the React tree is small enough that
 * a context or `@wordpress/data` store would be overkill. Switching
 * tabs unmounts the previous one, so each tab's data hooks and
 * resolvers are scoped to the time it's on screen (no stale
 * subscriptions, no background polling for hidden tabs).
 */
import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import {
	AdminNoticeSlot,
	Footer,
	Notices,
	PageBody,
	PageHeader,
	TabNav,
} from '../../common';
import useSettings from '../../hooks/use-settings';
import { resolveTabs } from './tabs';

const AdminApp = () => {
	// We block the first render of any tab on the initial settings
	// fetch. Most tabs don't strictly need the settings — addons /
	// support don't read them — but rendering a spinner while
	// core-data resolves prevents a flash of empty form controls
	// when the user lands on the default Settings tab.
	const { hasLoaded } = useSettings();

	// Resolve the tab registry on mount so addon bundles have a
	// chance to register via `loggedin.admin.tabs` before the filter
	// is read. Memoised so the lookup doesn't run every render.
	const tabs = useMemo( () => resolveTabs(), [] );

	// Ordered list of tab keys. The first entry is the default tab.
	const tabKeys = Object.keys( tabs );
	const [ current, setCurrent ] = useState( tabKeys[ 0 ] );

	// `TabNav` expects a simple `{ key: label }` map; build it from
	// the tab registry so adding a new tab is a one-entry change in
	// `./tabs/index.js`.
	const navs = Object.fromEntries(
		Object.entries( tabs ).map( ( [ key, tab ] ) => [ key, tab.label ] )
	);

	// Fall back to the first tab if `current` ever points at an
	// unknown key (e.g. an addon-registered tab was removed between
	// renders).
	const ActiveTab = ( tabs[ current ] || tabs[ tabKeys[ 0 ] ] ).component;

	// Some tabs opt into the wide page-body variant (Addons grid);
	// the default is the narrow 780px settings-form column.
	const isWide = !! tabs[ current ]?.wide;

	return (
		<>
			<PageHeader
				title={ __( 'Loggedin – Session Manager', 'loggedin' ) }
			>
				<TabNav
					current={ current }
					navs={ navs }
					onChange={ setCurrent }
				/>
			</PageHeader>

			<PageBody wide={ isWide }>
				<AdminNoticeSlot />

				{ ! hasLoaded ? (
					<div className="loggedin-page-loader">
						<Spinner />
					</div>
				) : (
					<>
						<ActiveTab />
						{ /*
						 * Sticky save bar is only relevant on the
						 * Settings tab — the other tabs either
						 * persist immediately (Addons license
						 * actions) or have nothing to persist
						 * (Support).
						 */ }
						{ current === 'settings' && <Footer /> }
					</>
				) }
			</PageBody>

			<Notices />
		</>
	);
};

export default AdminApp;
