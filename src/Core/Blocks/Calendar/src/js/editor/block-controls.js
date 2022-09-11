const { BlockControls, AlignmentToolbar } = wp.blockEditor;

export const Block = (props) => {
  const { attributes, setAttributes } = props;
  const { textAlignment } = attributes;

  return (
    <BlockControls>
      <AlignmentToolbar
        value={textAlignment}
        onChange={(textAlignment) => {
          setAttributes({ textAlignment });
        }}
      />
    </BlockControls>
  );
};
