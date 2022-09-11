const { __ } = wp.i18n;
const { PanelBody, ToggleControl, SelectControl, __experimentalInputControl, BaseControl } = wp.components;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;

export const Inspector = (props) => {
  const { attributes, setAttributes, isSelected, clientId } = props;
  const {
    bgColor,
    textColor,
    topSpacing,
    bottomSpacing,
  } = attributes;

  return (
    <InspectorControls>
      <PanelColorSettings
        title={__('Color Settings')}
        initialOpen={false}
        colorSettings={[
          {
            value: bgColor,
            onChange: (colorValue) => setAttributes({ bgColor: colorValue }),
            label: __('Background Color'),
          },
          {
            value: textColor,
            onChange: (colorValue) => setAttributes({ textColor: colorValue }),
            label: __('Text Color'),
          },
        ]}
      >
      </PanelColorSettings>
      <PanelBody title={__('Spacing')}>
        <ToggleControl
          label={__('Spacing Top')}
          help={topSpacing ? __('Block has top spacing.') : __('Block does not have top spacing.')}
          checked={topSpacing}
          onChange={(topSpacingValue) => {
            setAttributes({ topSpacing: topSpacingValue });
          }}
        />
        <ToggleControl
          label={__('Spacing Bottom')}
          help={bottomSpacing ? __('Block has bottom spacing.') : __('Block does not have bottom spacing.')}
          checked={bottomSpacing}
          onChange={(bottomSpacingValue) => {
            setAttributes({ bottomSpacing: bottomSpacingValue });
          }}
        />
      </PanelBody>
    </InspectorControls>
  )
}
