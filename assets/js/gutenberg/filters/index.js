import { addFilter } from '@wordpress/hooks';
import columnTaxSelector from './custom-taxonomies/column-tax-selector';

addFilter('editor.PostTaxonomyType', 'nucssa-core-editor-extensions', columnTaxSelector);
