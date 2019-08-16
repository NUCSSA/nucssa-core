import { registerBlockType } from '@wordpress/blocks';
import * as clubInfo from './club-info';


const registerBlock = block => {
  const {metadata, settings, name} = block;
  registerBlockType(name, settings);
}


[
  clubInfo
].forEach( registerBlock );
