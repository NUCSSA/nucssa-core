import { select } from '@wordpress/data';


/**
 * WARNING
 * This util function only works inside block or plugin component
 */
export function getCurrentPostType() {
  const currentPostType = select('core/editor').getCurrentPostType();
  return currentPostType;
}