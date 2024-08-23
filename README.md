## **Auto Featured Image**

**A WordPress Plugin for Automatically Setting Featured Images**

### **Overview**

This plugin simplifies the process of setting featured images for your WordPress posts by automatically identifying and setting the first image in a post as the featured image. It supports both images stored in the media library and external images, providing a more flexible and efficient way to manage your post visuals.

### **Key Changes in This Version**

- Removed the `add_image_to_library` method to avoid unnecessary media library additions.
- Modified `set_featured_image_by_url` to:
  - Set the image as the featured image if it's already in the media library.
  - Store the URL as post meta if the image is not in the media library.
- Added `external_thumbnail_html` to display external images as featured images.
- Updated the URL metabox to display the current external URL if set.

### **How it Works**

1. **Identifies First Image:** When a post is saved, the plugin scans the post content for the first image.
2. **Checks Media Library:** If the image is found in the media library, it sets it as the featured image.
3. **Handles External Images:** For external images, the plugin stores the URL as post meta and displays it as the featured image without adding it to the media library.
4. **Provides Meta Box:** A meta box allows you to manually set or update the featured image URL.

### **Installation**

1. **Download:** Download the latest version of the plugin from [GitHub repository link].
2. **Upload:** Upload the plugin's folder to your WordPress `wp-content/plugins/` directory.
3. **Activate:** Activate the plugin through the "Plugins" menu in your WordPress admin.

### **Usage**

- **Automatic Setting:** The plugin will automatically set the first image in a post as the featured image when you save it.
- **Manual Setting:** Use the meta box provided by the plugin to manually set or update the featured image URL.

### **Contributing**

Contributions are welcome! Please feel free to fork the repository, make your changes, and submit a pull request.

**License:** [MIT License]
