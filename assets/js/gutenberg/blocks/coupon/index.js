import metadata from './block.json';
import edit from './edit';

const { name, title, category, description, keywords, attributes } = metadata;

export { metadata, name };
export const settings = {
  title,
  description,
  category,
  icon: 'tickets-alt',
  keywords,
  supports: { html: true, },
  attributes,
  edit,
  useOnce: true,
  styles: [
    { name: 'style-1-pattern-1', label: 'Style1: Pattern1', isDefault: true },
    { name: 'style-1-pattern-2', label: 'Style1: Pattern2' },
    { name: 'style-1-pattern-3', label: 'Style1: Pattern3' },
    { name: 'style-1-pattern-4', label: 'Style1: Pattern4' },
    { name: 'style-2-pattern-1', label: 'Style2: Pattern1' },
  ]
};