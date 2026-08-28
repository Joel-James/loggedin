/**
 * Placeholder that receives WP core admin notices.
 *
 * WordPress echoes `admin_notices` output above `.wrap` on the page, so
 * by the time our React app mounts, any notice (including the wp.org
 * review prompt from `foxelabs/wp-review-notice`) sits above the plugin
 * header instead of between the header and the settings panels.
 *
 * On mount we relocate every WP-style notice that lives inside
 * `#wpbody-content` but outside our React root into this slot. The
 * effect runs once — WP notices are static HTML, so a one-shot move
 * is enough and avoids fighting other scripts that mutate the same
 * nodes later (e.g. the "dismiss" click handler in `common.js`).
 */
import { useEffect, useRef } from '@wordpress/element';

const AdminNoticeSlot = () => {
	const slotRef = useRef( null );

	useEffect( () => {
		const slot = slotRef.current;
		if ( ! slot ) {
			return;
		}

		// Every WP notice class we care about. `.updated` and `.error`
		// are the legacy classes still emitted by a few plugins;
		// `.notice` is the modern one used by the review library.
		const selector = '.notice, .updated, .error';

		document
			.querySelectorAll( `#wpbody-content ${ selector }` )
			.forEach( ( node ) => {
				// Skip anything already inside our slot (idempotent
				// against React re-mounts in dev) or inline notices
				// that plugins deliberately position themselves.
				if (
					slot.contains( node ) ||
					node.classList.contains( 'inline' )
				) {
					return;
				}
				slot.appendChild( node );
			} );
	}, [] );

	return <div ref={ slotRef } className="loggedin-admin-notices" />;
};

export default AdminNoticeSlot;
