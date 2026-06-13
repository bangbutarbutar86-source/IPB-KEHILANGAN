import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';
import '../services/theme_service.dart';

class UploadReportScreen extends StatefulWidget {
  const UploadReportScreen({super.key, required this.initialImage});

  final XFile initialImage;

  @override
  State<UploadReportScreen> createState() => _UploadReportScreenState();
}

class _UploadReportScreenState extends State<UploadReportScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  final picker = ImagePicker();
  final titleController = TextEditingController();
  final locationController = TextEditingController();
  final descriptionController = TextEditingController();
  final formKey = GlobalKey<FormState>();

  late List<XFile> images;
  String selectedType = 'Ditemukan';
  bool isPosting = false;

  @override
  void initState() {
    super.initState();
    images = [widget.initialImage];
  }

  @override
  void dispose() {
    titleController.dispose();
    locationController.dispose();
    descriptionController.dispose();
    super.dispose();
  }

  Future<void> addFromCamera() async {
    if (images.length >= 3) {
      showMessage("Maksimal 3 foto");
      return;
    }

    final image = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 85,
    );
    if (image == null) return;

    setState(() => images.add(image));
  }

  Future<void> addFromGallery() async {
    if (images.length >= 3) {
      showMessage("Maksimal 3 foto");
      return;
    }

    final selectedImages = await picker.pickMultiImage(imageQuality: 85);
    if (selectedImages.isEmpty) return;

    final slotsLeft = 3 - images.length;
    setState(() => images.addAll(selectedImages.take(slotsLeft)));
  }

  void removeImage(int index) {
    if (images.length == 1) {
      showMessage("Minimal 1 foto wajib dipilih");
      return;
    }

    setState(() => images.removeAt(index));
  }

  Future<void> submitReport() async {
    FocusScope.of(context).unfocus();

    if (!formKey.currentState!.validate()) {
      return;
    }

    if (images.isEmpty) {
      showMessage("Minimal 1 foto wajib dipilih");
      return;
    }

    setState(() => isPosting = true);

    try {
      await ApiService.createReport(
        {
          "title": titleController.text.trim(),
          "location": locationController.text.trim(),
          "description": descriptionController.text.trim(),
          "type": selectedType,
        },
        images,
      );

      if (!mounted) return;

      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;

      showMessage(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) {
        setState(() => isPosting = false);
      }
    }
  }

  void showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        return Scaffold(
          backgroundColor: isDarkMode ? const Color(0xFF111827) : Colors.white,
          body: SafeArea(
            child: Form(
          key: formKey,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(12, 18, 12, 28),
            children: [
              _Header(onBack: () => Navigator.pop(context, false)),
              const SizedBox(height: 18),
              _PhotoPreview(
                image: images.first,
                onCameraTap: addFromCamera,
                onGalleryTap: addFromGallery,
              ),
              const SizedBox(height: 10),
              _PhotoStrip(
                images: images,
                onRemove: removeImage,
                onAddCamera: addFromCamera,
                onAddGallery: addFromGallery,
              ),
              const SizedBox(height: 10),
              _ReportTextField(
                controller: titleController,
                hintText: "Nama Barang",
                validatorText: "Nama barang wajib diisi",
              ),
              const SizedBox(height: 10),
              _ReportTextField(
                controller: locationController,
                hintText: "Lokasi Dicari/Ditemukan",
                validatorText: "Lokasi wajib diisi",
              ),
              const SizedBox(height: 10),
              _ReportTextField(
                controller: descriptionController,
                hintText: "Ciri Ciri/Deskripsi",
                validatorText: "Deskripsi wajib diisi",
                minLines: 4,
                maxLines: 6,
              ),
              const SizedBox(height: 16),
              _TypeDropdown(
                value: selectedType,
                onChanged: (value) {
                  if (value == null) return;
                  setState(() => selectedType = value);
                },
              ),
              const SizedBox(height: 56),
              SizedBox(
                height: 40,
                child: ElevatedButton(
                  onPressed: isPosting ? null : submitReport,
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: primaryBlue,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: primaryBlue.withAlpha(140),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(22),
                    ),
                  ),
                  child: isPosting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          "POSTING SEKARANG",
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),
            ],
          ),
            ),
          ),
        );
      },
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.onBack});

  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        IconButton(
          onPressed: onBack,
          icon: const Icon(Icons.chevron_left, size: 30),
          tooltip: "Kembali",
          padding: EdgeInsets.zero,
          constraints: const BoxConstraints.tightFor(width: 32, height: 32),
        ),
        const SizedBox(width: 6),
        const Expanded(
          child: Text(
            "Lapor Barang Hilang",
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}

class _PhotoPreview extends StatelessWidget {
  const _PhotoPreview({
    required this.image,
    required this.onCameraTap,
    required this.onGalleryTap,
  });

  final XFile image;
  final VoidCallback onCameraTap;
  final VoidCallback onGalleryTap;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(18),
      child: Stack(
        children: [
          SizedBox(
            height: 244,
            width: double.infinity,
            child: _PickedImage(image: image, fit: BoxFit.cover),
          ),
          Positioned(
            right: 12,
            bottom: 12,
            child: Row(
              children: [
                _PhotoActionButton(
                  icon: Icons.photo_library_outlined,
                  tooltip: "Tambah dari galeri",
                  onTap: onGalleryTap,
                ),
                const SizedBox(width: 10),
                _PhotoActionButton(
                  icon: Icons.photo_camera_outlined,
                  tooltip: "Tambah dari kamera",
                  onTap: onCameraTap,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PhotoStrip extends StatelessWidget {
  const _PhotoStrip({
    required this.images,
    required this.onRemove,
    required this.onAddCamera,
    required this.onAddGallery,
  });

  final List<XFile> images;
  final ValueChanged<int> onRemove;
  final VoidCallback onAddCamera;
  final VoidCallback onAddGallery;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 68,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: images.length + (images.length < 3 ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          if (index == images.length) {
            return _AddPhotoTile(
              onAddCamera: onAddCamera,
              onAddGallery: onAddGallery,
            );
          }

          return _ThumbnailTile(
            image: images[index],
            onRemove: () => onRemove(index),
          );
        },
      ),
    );
  }
}

class _ThumbnailTile extends StatelessWidget {
  const _ThumbnailTile({required this.image, required this.onRemove});

  final XFile image;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: SizedBox(
            width: 68,
            height: 68,
            child: _PickedImage(image: image, fit: BoxFit.cover),
          ),
        ),
        Positioned(
          top: -6,
          right: -6,
          child: GestureDetector(
            onTap: onRemove,
            child: Container(
              width: 22,
              height: 22,
              decoration: const BoxDecoration(
                color: Colors.black87,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.close, color: Colors.white, size: 15),
            ),
          ),
        ),
      ],
    );
  }
}

class _AddPhotoTile extends StatelessWidget {
  const _AddPhotoTile({
    required this.onAddCamera,
    required this.onAddGallery,
  });

  final VoidCallback onAddCamera;
  final VoidCallback onAddGallery;

  @override
  Widget build(BuildContext context) {
    return PopupMenuButton<String>(
      tooltip: "Tambah foto",
      onSelected: (value) {
        if (value == 'camera') {
          onAddCamera();
        } else {
          onAddGallery();
        }
      },
      itemBuilder: (_) => const [
        PopupMenuItem(value: 'camera', child: Text("Kamera")),
        PopupMenuItem(value: 'gallery', child: Text("Galeri")),
      ],
      child: Container(
        width: 68,
        height: 68,
        decoration: BoxDecoration(
          color: const Color(0xFFE5E5E5),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFBDBDBD)),
        ),
        child: const Icon(
          Icons.add_photo_alternate_outlined,
          color: Color(0xFF555555),
          size: 30,
        ),
      ),
    );
  }
}

class _PickedImage extends StatelessWidget {
  const _PickedImage({required this.image, required this.fit});

  final XFile image;
  final BoxFit fit;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Uint8List>(
      future: image.readAsBytes(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          return Container(
            color: const Color(0xFFD9D9D9),
            child: const Center(child: CircularProgressIndicator()),
          );
        }

        return Image.memory(
          snapshot.data!,
          fit: fit,
          errorBuilder: (_, __, ___) => const _ImageFallback(),
        );
      },
    );
  }
}

class _ImageFallback extends StatelessWidget {
  const _ImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFD9D9D9),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFF777777), size: 54),
      ),
    );
  }
}

class _PhotoActionButton extends StatelessWidget {
  const _PhotoActionButton({
    required this.icon,
    required this.tooltip,
    required this.onTap,
  });

  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: Material(
        color: Colors.black.withAlpha(158),
        shape: const CircleBorder(),
        child: InkWell(
          onTap: onTap,
          customBorder: const CircleBorder(),
          child: SizedBox(
            width: 42,
            height: 42,
            child: Icon(icon, color: Colors.white, size: 24),
          ),
        ),
      ),
    );
  }
}

class _ReportTextField extends StatelessWidget {
  const _ReportTextField({
    required this.controller,
    required this.hintText,
    required this.validatorText,
    this.minLines = 1,
    this.maxLines = 1,
  });

  final TextEditingController controller;
  final String hintText;
  final String validatorText;
  final int minLines;
  final int maxLines;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      minLines: minLines,
      maxLines: maxLines,
      validator: (value) {
        if (value == null || value.trim().isEmpty) {
          return validatorText;
        }

        return null;
      },
      decoration: InputDecoration(
        hintText: hintText,
        hintStyle: const TextStyle(color: Color(0xFF999999), fontSize: 14),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(color: Color(0xFF9D9D9D)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(
            color: _UploadReportScreenState.primaryBlue,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(color: Colors.red),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(color: Colors.red),
        ),
      ),
    );
  }
}

class _TypeDropdown extends StatelessWidget {
  const _TypeDropdown({
    required this.value,
    required this.onChanged,
  });

  final String value;
  final ValueChanged<String?> onChanged;

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField<String>(
      value: value,
      items: const [
        DropdownMenuItem(value: "Ditemukan", child: Text("Ditemukan")),
        DropdownMenuItem(value: "Dicari", child: Text("Dicari")),
      ],
      onChanged: onChanged,
      icon: const Icon(Icons.keyboard_arrow_down),
      decoration: InputDecoration(
        hintText: "Ditemukan/Dicari",
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(color: Color(0xFF9D9D9D)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(5),
          borderSide: const BorderSide(
            color: _UploadReportScreenState.primaryBlue,
          ),
        ),
      ),
    );
  }
}
