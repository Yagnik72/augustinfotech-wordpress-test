const { registerBlockType } = wp.blocks;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl } = wp.components;
const { __ } = wp.i18n;
const ServerSideRender = wp.serverSideRender;

registerBlockType('wp-referral-system/top-blogs', {
    edit: function Edit({ attributes, setAttributes }) {
        const { orderBy, order, numberOfPosts } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Block Settings', 'wp-referral-system')}>
                        <SelectControl
                            label={__('Order By', 'wp-referral-system')}
                            value={orderBy}
                            options={[
                                { label: __('ASC', 'wp-referral-system'), value: 'ASC' },
                                { label: __('DESC', 'wp-referral-system'), value: 'DESC' }
                            ]}
                            onChange={(value) => setAttributes({ orderBy: value })}
                        />
                        <SelectControl
                            label={__('Order', 'wp-referral-system')}
                            value={order}
                            options={[
                                { label: __('Name', 'wp-referral-system'), value: 'name' },
                                { label: __('Publish Date', 'wp-referral-system'), value: 'publishDate' }
                            ]}
                            onChange={(value) => setAttributes({ order: value })}
                        />
                        <RangeControl
                            label={__('Number of Posts to Display', 'wp-referral-system')}
                            value={numberOfPosts}
                            onChange={(value) => setAttributes({ numberOfPosts: value })}
                            min={1}
                            max={10}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <ServerSideRender
                        block="wp-referral-system/top-blogs"
                        attributes={attributes}
                    />
                </div>
            </>
        );
    }
}); 