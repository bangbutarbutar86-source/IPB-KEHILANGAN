import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';
import '../services/theme_service.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key, required this.user});

  final Map<String, dynamic> user;

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  final picker = ImagePicker();
  final formKey = GlobalKey<FormState>();
  late final TextEditingController nameController;
  late final TextEditingController phoneController;
  late final TextEditingController emailController;
  late final TextEditingController nimController;

  // ✅ Ganti genderController jadi String langsung
  late String _selectedGender;
  static const List<String> _genderOptions = ['Laki-Laki', 'Perempuan'];

  XFile? selectedPhoto;
  bool isSaving = false;

  @override
  void initState() {
    super.initState();
    nameController = TextEditingController(
      text: (widget.user['name'] ?? '').toString(),
    );
    phoneController = TextEditingController(
      text: (widget.user['phone'] ?? '').toString(),
    );
    emailController = TextEditingController(
      text: (widget.user['email'] ?? '').toString(),
    );
    nimController = TextEditingController(
      text: (widget.user['nim'] ?? '').toString(),
    );

    // ✅ Set default gender, fallback ke 'Laki-Laki' kalau tidak valid
    final savedGender = (widget.user['gender'] ?? '').toString();
    _selectedGender = _genderOptions.contains(savedGender)
        ? savedGender
        : 'Laki-Laki';
  }

  @override
  void dispose() {
    nameController.dispose();
    phoneController.dispose();
    emailController.dispose();
    nimController.dispose();
    super.dispose();
  }

  Future<void> pickProfilePhoto() async {
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_camera_outlined),
              title: const Text("Kamera"),
              onTap: () => Navigator.pop(context, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library_outlined),
              title: const Text("Galeri"),
              onTap: () => Navigator.pop(context, ImageSource.gallery),
            ),
          ],
        ),
      ),
    );

    if (source == null) return;

    final photo = await picker.pickImage(source: source, imageQuality: 85);
    if (photo == null) return;

    setState(() => selectedPhoto = photo);
  }

  Future<void> saveProfile() async {
    if (!formKey.currentState!.validate()) return;

    setState(() => isSaving = true);

    try {
      final updatedUser = await ApiService.updateProfile(
        data: {
          "name": nameController.text.trim(),
          "phone": phoneController.text.trim(),
          "gender": _selectedGender, // ✅ pakai _selectedGender
          "nim": nimController.text.trim(),
        },
        profilePhoto: selectedPhoto,
      );

      if (!mounted) return;

      Navigator.pop(context, updatedUser);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() => isSaving = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        final bgColor = isDarkMode ? const Color(0xFF111827) : Colors.white;
        final textColor = isDarkMode ? Colors.white : Colors.black;
        final mutedColor =
            isDarkMode ? const Color(0xFFB8B8B8) : const Color(0xFF9B9B9B);

        return Scaffold(
          backgroundColor: bgColor,
          body: SafeArea(
            child: Form(
              key: formKey,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(12, 16, 12, 24),
                children: [
                  Align(
                    alignment: Alignment.centerLeft,
                    child: TextButton.icon(
                      onPressed: () => Navigator.pop(context),
                      icon: Icon(Icons.chevron_left, color: textColor, size: 30),
                      label: const Text(
                        "Back",
                        style: TextStyle(
                          color: primaryBlue,
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    "Profil Saya",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: textColor,
                      fontSize: 25,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 30),
                  Center(
                    child: _EditableAvatar(
                      photoUrl: (widget.user['profile_photo'] ?? '').toString(),
                      selectedPhoto: selectedPhoto,
                      onAddPhoto: pickProfilePhoto,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    emailController.text,
                    textAlign: TextAlign.center,
                    style: TextStyle(color: mutedColor, fontSize: 16),
                  ),
                  const SizedBox(height: 18),
                  _ProfileInput(
                    label: "NAMA LENGKAP",
                    icon: Icons.badge_outlined,
                    controller: nameController,
                    isDarkMode: isDarkMode,
                  ),
                  _ProfileInput(
                    label: "NOMOR TELEPON",
                    icon: Icons.call,
                    controller: phoneController,
                    keyboardType: TextInputType.phone,
                    isDarkMode: isDarkMode,
                  ),
                  _ProfileInput(
                    label: "EMAIL",
                    icon: Icons.mail,
                    controller: emailController,
                    enabled: false,
                    isDarkMode: isDarkMode,
                  ),

                  // ✅ KELAMIN pakai dropdown, bukan text field
                  _GenderDropdown(
                    value: _selectedGender,
                    options: _genderOptions,
                    isDarkMode: isDarkMode,
                    onChanged: (val) {
                      if (val != null) setState(() => _selectedGender = val);
                    },
                  ),

                  _ProfileInput(
                    label: "NIM",
                    icon: Icons.assignment_turned_in_outlined,
                    controller: nimController,
                    isDarkMode: isDarkMode,
                  ),
                  const SizedBox(height: 28),
                  SizedBox(
                    height: 70,
                    child: OutlinedButton(
                      onPressed: isSaving ? null : saveProfile,
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: primaryBlue),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(30),
                        ),
                      ),
                      child: isSaving
                          ? const CircularProgressIndicator()
                          : const Text(
                              "Simpan Perubahan",
                              style: TextStyle(
                                color: primaryBlue,
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
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

// ✅ Widget dropdown khusus Kelamin
class _GenderDropdown extends StatelessWidget {
  const _GenderDropdown({
    required this.value,
    required this.options,
    required this.isDarkMode,
    required this.onChanged,
  });

  final String value;
  final List<String> options;
  final bool isDarkMode;
  final ValueChanged<String?> onChanged;

  @override
  Widget build(BuildContext context) {
    final textColor = isDarkMode ? Colors.white : Colors.black;
    final fieldColor = isDarkMode ? const Color(0xFF1F2937) : Colors.white;
    final labelColor =
        isDarkMode ? const Color(0xFFB8B8B8) : const Color(0xFF9B9B9B);

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 4, bottom: 2),
            child: Text(
              "KELAMIN",
              style: TextStyle(color: labelColor, fontSize: 13),
            ),
          ),
          DropdownButtonFormField<String>(
            value: value,
            onChanged: onChanged,
            dropdownColor: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
            icon: Icon(Icons.keyboard_arrow_down_rounded,
                color: const Color(0xFF747474), size: 28),
            style: TextStyle(color: textColor, fontSize: 20),
            decoration: InputDecoration(
              filled: true,
              fillColor: fieldColor,
              prefixIcon: const Icon(Icons.wc, color: Color(0xFF747474), size: 30),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 14,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Color(0xFF8B8B8B)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(
                    color: _EditProfileScreenState.primaryBlue),
              ),
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.red),
              ),
              focusedErrorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.red),
              ),
            ),
            items: options
                .map((gender) => DropdownMenuItem(
                      value: gender,
                      child: Text(gender,
                          style:
                              TextStyle(color: textColor, fontSize: 20)),
                    ))
                .toList(),
          ),
        ],
      ),
    );
  }
}

class _EditableAvatar extends StatelessWidget {
  const _EditableAvatar({
    required this.photoUrl,
    required this.selectedPhoto,
    required this.onAddPhoto,
  });

  final String photoUrl;
  final XFile? selectedPhoto;
  final VoidCallback onAddPhoto;

  @override
  Widget build(BuildContext context) {
    Widget avatar = const CircleAvatar(
      radius: 98,
      backgroundColor: _EditProfileScreenState.primaryBlue,
      child: Icon(Icons.person, color: Colors.white, size: 132),
    );

    if (photoUrl.isNotEmpty && selectedPhoto == null) {
      avatar = ClipOval(
        child: Image.network(
          photoUrl,
          width: 196,
          height: 196,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => const CircleAvatar(
            radius: 98,
            backgroundColor: _EditProfileScreenState.primaryBlue,
            child: Icon(Icons.person, color: Colors.white, size: 132),
          ),
        ),
      );
    }

    if (selectedPhoto != null) {
      avatar = ClipOval(
        child: FutureBuilder<Uint8List>(
          future: selectedPhoto!.readAsBytes(),
          builder: (context, snapshot) {
            if (!snapshot.hasData) {
              return const CircleAvatar(
                radius: 98,
                backgroundColor: _EditProfileScreenState.primaryBlue,
              );
            }
            return Image.memory(
              snapshot.data!,
              width: 196,
              height: 196,
              fit: BoxFit.cover,
            );
          },
        ),
      );
    }

    return Stack(
      clipBehavior: Clip.none,
      children: [
        avatar,
        Positioned(
          right: 8,
          bottom: 26,
          child: GestureDetector(
            onTap: onAddPhoto,
            child: Container(
              width: 44,
              height: 44,
              decoration: const BoxDecoration(
                color: Colors.black,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.add, color: Colors.white, size: 34),
            ),
          ),
        ),
      ],
    );
  }
}

class _ProfileInput extends StatelessWidget {
  const _ProfileInput({
    required this.label,
    required this.icon,
    required this.controller,
    required this.isDarkMode,
    this.enabled = true,
    this.keyboardType,
  });

  final String label;
  final IconData icon;
  final TextEditingController controller;
  final bool isDarkMode;
  final bool enabled;
  final TextInputType? keyboardType;

  @override
  Widget build(BuildContext context) {
    final textColor = isDarkMode ? Colors.white : Colors.black;
    final fieldColor = isDarkMode ? const Color(0xFF1F2937) : Colors.white;
    final labelColor =
        isDarkMode ? const Color(0xFFB8B8B8) : const Color(0xFF9B9B9B);

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 4, bottom: 2),
            child: Text(
              label,
              style: TextStyle(color: labelColor, fontSize: 13),
            ),
          ),
          TextFormField(
            controller: controller,
            enabled: enabled,
            keyboardType: keyboardType,
            style: TextStyle(
                color: enabled ? textColor : labelColor, fontSize: 20),
            validator: (value) {
              if (!enabled) return null;
              if (value == null || value.trim().isEmpty) {
                return "$label wajib diisi";
              }
              return null;
            },
            decoration: InputDecoration(
              filled: true,
              fillColor: fieldColor,
              prefixIcon:
                  Icon(icon, color: const Color(0xFF747474), size: 30),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 14,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Color(0xFF8B8B8B)),
              ),
              disabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Color(0xFF8B8B8B)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(
                    color: _EditProfileScreenState.primaryBlue),
              ),
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.red),
              ),
              focusedErrorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.red),
              ),
            ),
          ),
        ],
      ),
    );
  }
}