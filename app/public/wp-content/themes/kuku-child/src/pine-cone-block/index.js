import { registerBlockType } from "@wordpress/blocks";

registerBlockType("kuku/pine-cone-block", {
  title: "Pine Cone Block",
  icon: "smiley",
  category: "widgets",
  edit: ({ className }) => {
    return (
      <div className={className}>
        <h3>Pine Cone of the Day</h3>
        <img
          src="/wp-content/themes/kuku-child/assets/pinecones/pine_cone1.png"
          alt="Pine Cone"
        />
      </div>
    );
  },
  save: () => {
    return (
      <div>
        <h3>Pine Cone of the Day</h3>
        <img
          src="/wp-content/themes/kuku-child/assets/pinecones/pine_cone1.png"
          alt="Pine Cone"
        />
      </div>
    );
  },
});
