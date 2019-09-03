import { registerBlockType } from '@wordpress/blocks';
import * as clubInfo from './club-info';
import * as coupon from './coupon';


const registerBlock = block => {
  const {settings, name} = block;
  registerBlockType(name, settings);
}


[
  clubInfo,
  coupon
].forEach( registerBlock );
