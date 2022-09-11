import edit from './edit';
import save from './save';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const block_namespace = custom_block.namespace.replace(/\s+/g, '').toLowerCase();
const block_name      = custom_block.block_name.replace(/\s+/g, '-').toLowerCase();
const block_category  = custom_block.block_category_slug;
const full_width      = custom_block.full_width;

registerBlockType( block_namespace + '/' + block_name, {
  title: custom_block.namespace + ' - ' + custom_block.block_name,
  icon: {
    src: custom_block.icon,
    background: custom_block.background,
    foreground: custom_block.foreground,
  },
  category: block_category,
  description: custom_block.block_description,
  attributes: custom_block.block_attributes,
  supports: { anchor: false },

  // Enables full width block in backend editor
  getEditWrapperProps( attributes ) {
    if ( full_width == 'yes' ) {
      attributes['align'] = 'full';
      return {
        attributes,
        'data-align': 'full'
      };
    }
  },

  edit,
  save,
} );
