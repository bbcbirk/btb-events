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
    wpquery,
    displayAddToCalendar,
  } = attributes;

  console.log(wpquery);
  return (
    <InspectorControls>
      <PanelBody title={__('Events')}>
        <SelectControl
          label={__('Order')}
          value={wpquery?.order}
          options={[
            { label: __('Descending'), value: 'desc' },
            { label: __('Ascending'), value: 'asc' },
          ]}
          onChange={(value) => {
            let obj = { ...wpquery, order: value }
            setAttributes({ wpquery: obj });
          }}
          __nextHasNoMarginBottom
        />
        <SelectControl
          label={__('Order By')}
          value={wpquery?.orderBy}
          options={[
            { label: __('Date'), value: 'date' },
            { label: __('Title'), value: 'title' },
          ]}
          onChange={(value) => {
            let obj = { ...wpquery, orderBy: value }
            setAttributes({ wpquery: obj });
          }}
          __nextHasNoMarginBottom
        />
        <BaseControl>
          <__experimentalInputControl
            label={__('Number of Events')}
            value={wpquery?.numberOfItems}
            type={'number'}
            min={'1'}
            max={'100'}
            step={'1'}
            onChange={(value) => {
              let obj = { ...wpquery, numberOfItems: value }
              setAttributes({ wpquery: obj });
            }}
          />
        </BaseControl>
        <BaseControl>
          <__experimentalInputControl
            label={__('Offset')}
            value={wpquery?.offset}
            type={'number'}
            min={'1'}
            max={'100'}
            step={'1'}
            onChange={(value) => {
              let obj = { ...wpquery, offset: value }
              setAttributes({ wpquery: obj });
            }}
          />
        </BaseControl>
        <ToggleControl
          label={__('Add to calendar button')}
          help={displayAddToCalendar ? __('Display add to calendar button.') : __('Hide add to calendar button.')}
          checked={displayAddToCalendar}
          onChange={(displayAddToCalendarValue) => {
            setAttributes({ displayAddToCalendar: displayAddToCalendarValue });
          }}
        />
      </PanelBody>
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
