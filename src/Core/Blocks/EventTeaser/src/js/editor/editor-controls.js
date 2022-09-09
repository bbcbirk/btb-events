const { __ } = wp.i18n;

const { RichText } = wp.blockEditor;
const { ServerSideRender, Disabled } = wp.components;
const { Fragment } = wp.element;

export const Editor = (props) => {
  const { attributes, setAttributes, isSelected } = props;
  const {
    blockId,
    bgColor,
    textColor,
    bottomSpacing,
    topSpacing,
    className,
    title,
    textAlignment,
  } = attributes;

  return (
    <Fragment>
      <div
        className={`${className ? ' ' + className : ''} btb-blocks event_teaser event_teaser--${blockId}${bgColor ? ' has-background' : ''}${topSpacing ? ' has-top-spacing' : ''}${bottomSpacing ? ' has-bottom-spacing' : ''} btb-block-editor`}
        style={{
          backgroundColor: bgColor,
          color: textColor,
        }}
      >
        <div className="btb-blocks__inner event_teaser__inner">
          <div style={{
            textAlign: textAlignment,
          }}>
            <RichText
              tagName="h2"
              className="btb-event_teaser__title"
              placeholder={__("Add Title text...")}
              value={title}
              onChange={(titleValue) => {
                setAttributes({ title: titleValue });
              }}
              style={{
                color: textColor,
              }}
              keepPlaceholderOnFocus
              allowedFormats={[]}
            />
          </div>
          <Disabled>
            <ServerSideRender
              block="btb/event-teaser"
              attributes={{
                renderFromServer: true
              }}
            />
          </Disabled>
        </div>
      </div>
    </Fragment>
  )
}