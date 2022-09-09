const { __ } = wp.i18n;
const { TextControl } = wp.components;
const { InspectorAdvancedControls } = wp.blockEditor;

export const InspectorAdvanced = (props) => {
  const { attributes, setAttributes } = props;
  const { htmlAnchor } = attributes;
  const ANCHOR_REGEX = /[\s#]/g;

  return (
    <InspectorAdvancedControls>
      <TextControl
        label={__('HTML Anchor')}
        help={__('Anchors lets you link directly to a section on a page.')}
        value={htmlAnchor}
        onChange={(anchor) => {
          anchor = anchor.replace(ANCHOR_REGEX, '-');
          setAttributes({ htmlAnchor: anchor });
        }}
      />
    </InspectorAdvancedControls>
  )
}
