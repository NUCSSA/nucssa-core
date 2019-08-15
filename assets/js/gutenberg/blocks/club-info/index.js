import metadata from './block.json';
import icon from './icon';
import edit from './edit';
import save from './save';

const { name, category, attributes } = metadata;

export { metadata, name };
export const settings = {
  title: '社团基本信息',
  description: '社团基本信息，用于社团列表界面陈列展示',
  category,
  icon,
  keywords: ['club', 'nucssa'],
  supports: { html: false },
  attributes,
  edit,
  save,
};