// WordPress
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { getBlockDefaultClassName, registerBlockType } from '@wordpress/blocks';
import { _x } from '@wordpress/i18n';

const blockName = 'wdpln/example-block';
const classNameBase = getBlockDefaultClassName( blockName );

registerBlockType( blockName, {
    apiVersion: 2,
    title: _x( 'Beispiel Block', 'Block title', 'wdpln' ),
    description: _x( 'Block mit einem Titel und einem Text', 'Block title', 'wdpln' ),
    icon: 'heading',
    category: 'design',
    supports: {
        align: false,
        html: false,
    },
    example: {
        attributes: {
            title: 'Lorem ipsum dolor<br>Sit amet consectetur<br>Adipisicing elit sed<br>Do eiusmod tempor<br>Incididunt ut labore',
            text: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
        },
    },
    attributes: {
        text: {
            type: 'string',
        },
        title: {
            type: 'string',
        },
    },
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();
        const classNameBase = getBlockDefaultClassName( blockName );

        const { text, title } = attributes;

        return [
            <div { ...blockProps }>
            <div className={ `${classNameBase}__inner` }>
            <div className={ `${classNameBase}__contentwrap` }>
            <RichText
                tagName='p'
                placeholder={_x(
                        'Schreiben Sie eine Überschrift…',
                        'Field placeholder',
                        'sha'
                    )
                }
                className={ `${classNameBase}__title` }
                value={ title }
                allowedFormats={ [] }
                multiline={ false }
                keepPlaceholderOnFocus={ true }
                onChange={value => {
                        setAttributes({ title: value });
                    }
                }
            />
            <RichText
                tagName='p'
                placeholder={
                    _x(
                        'Schreiben Sie einen Text…',
                        'Field placeholder',
                        'sha'
                    )
                }
                className={ `${classNameBase}__text` }
                value={ text }
                allowedFormats={ [] }
                multiline={ false }
                keepPlaceholderOnFocus={ true }
                onChange={ value => {
                        setAttributes({ text: value });
                    }
                }
            />
            </div>
            </div>
            </div>
        ];
    },
    save( { attributes } ) {
        const blockProps = useBlockProps.save();
        const classNameBase = getBlockDefaultClassName( blockName );

        const { text, title } = attributes;

        return (
            <div { ...blockProps }>
            <div className={ `${classNameBase}__contentwrap` } >
            <RichText.Content className={ `${classNameBase}__title` } value={ title } tagName='p' />
            <RichText.Content className={ `${classNameBase}__text` } value={ text } tagName='p' />
            </div>
            </div>
        );
    },
} );