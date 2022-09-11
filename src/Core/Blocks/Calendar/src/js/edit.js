import { Block } from './editor/block-controls';
import { Editor } from './editor/editor-controls';
import { Inspector } from './editor/inspector-controls';
import { InspectorAdvanced } from './editor/inspector_advanced-controls';

const { Fragment } = wp.element;

function edit( props ) {
  const { attributes, setAttributes, clientId } = props;

  const { blockId } = attributes;

  if ( blockId !== clientId ) {
    setAttributes( { blockId: 'block_' + clientId } );
  }

  return (
    <Fragment>
      { Block( props ) }
      { Editor( props ) }
      { Inspector( props ) }
      { InspectorAdvanced( props ) }
    </Fragment>
  );
}

export default edit;
